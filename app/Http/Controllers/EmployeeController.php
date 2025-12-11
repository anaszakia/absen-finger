<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::orderBy('created_at', 'desc')->paginate(15);
        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|unique:employees,employee_id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'join_date' => 'nullable|date',
            'basic_salary' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('employees', 'public');
        }

        $employee = Employee::create($validated);

        // Sync to all active fingerprint machines
        $this->syncEmployeeToMachines($employee);

        return redirect()->route('employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan dan disinkronkan ke mesin fingerprint.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load('attendances');
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_id' => 'required|unique:employees,employee_id,' . $employee->id,
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'join_date' => 'nullable|date',
            'basic_salary' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }
            $validated['photo'] = $request->file('photo')->store('employees', 'public');
        }

        $employee->update($validated);

        // Sync to all active fingerprint machines
        $this->syncEmployeeToMachines($employee);

        return redirect()->route('employees.index')
            ->with('success', 'Data karyawan berhasil diupdate dan disinkronkan ke mesin fingerprint.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        // Delete from fingerprint machines
        $this->deleteEmployeeFromMachines($employee);

        // Delete photo if exists
        if ($employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Karyawan berhasil dihapus dari database dan mesin fingerprint.');
    }

    /**
     * Sync employee to all active fingerprint machines
     */
    private function syncEmployeeToMachines(Employee $employee)
    {
        $machines = AttendanceMachine::active()->get();

        foreach ($machines as $machine) {
            try {
                $zk = new \Rats\Zkteco\Lib\ZKTeco($machine->ip_address, $machine->port);
                
                if ($zk->connect()) {
                    // Set user with employee_id as UID
                    $zk->setUser(
                        $employee->employee_id,
                        0, // user ID (can be same as employee_id)
                        $employee->name,
                        '', // password (empty for fingerprint)
                        0, // role (0 = user)
                        '' // card number
                    );
                    
                    $zk->disconnect();
                }
            } catch (\Exception $e) {
                // Log error but continue with other machines
                \Log::error("Failed to sync employee {$employee->id} to machine {$machine->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Delete employee from all active fingerprint machines
     */
    private function deleteEmployeeFromMachines(Employee $employee)
    {
        $machines = AttendanceMachine::active()->get();

        foreach ($machines as $machine) {
            try {
                $zk = new \Rats\Zkteco\Lib\ZKTeco($machine->ip_address, $machine->port);
                
                if ($zk->connect()) {
                    $zk->deleteUser($employee->employee_id);
                    $zk->disconnect();
                }
            } catch (\Exception $e) {
                \Log::error("Failed to delete employee {$employee->id} from machine {$machine->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Sync employees from all machines via push server
     * This will wait for machines to push their user data
     */
    public function syncFromMachines()
    {
        try {
            $machines = AttendanceMachine::active()->get();
            
            if ($machines->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada mesin absensi yang aktif'
                ], 400);
            }

            // Informasi untuk user
            $info = [
                'success' => true,
                'message' => 'Sistem siap menerima data karyawan dari mesin',
                'machines_count' => $machines->count(),
                'machines' => $machines->map(function($machine) {
                    return [
                        'name' => $machine->name,
                        'ip' => $machine->ip_address,
                        'location' => $machine->location,
                    ];
                }),
                'instructions' => [
                    '1. Pastikan mesin absensi sudah dikonfigurasi untuk push data ke server',
                    '2. IP Server: ' . request()->getHttpHost(),
                    '3. Port: ' . request()->getPort(),
                    '4. URL: ' . route('attendance.push.receive'),
                    '5. Mesin akan otomatis mengirim data karyawan saat ada perubahan',
                    '6. Refresh halaman ini setelah beberapa saat untuk melihat data terbaru'
                ],
                'note' => 'Sistem menggunakan arsitektur PUSH. Mesin akan mengirim data otomatis ke server ini.'
            ];

            return response()->json($info);

        } catch (\Exception $e) {
            \Log::error('Error in syncFromMachines: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
