<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\AttendanceMachine;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display attendance recap
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['employee', 'attendanceMachine']);

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        } else {
            // Default to current month
            $query->whereMonth('date', now()->month)
                  ->whereYear('date', now()->year);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->byEmployee($request->employee_id);
        }

        $attendances = $query->orderBy('date', 'desc')
                             ->orderBy('check_in', 'desc')
                             ->paginate(20);

        $employees = Employee::active()->get();

        return view('attendances.index', compact('attendances', 'employees'));
    }

    /**
     * Sync attendance from specific machine
     */
    public function syncFromMachine(Request $request, AttendanceMachine $machine)
    {
        try {
            $zk = new \Rats\Zkteco\Lib\ZKTeco($machine->ip_address, $machine->port);
            
            if (!$zk->connect()) {
                return back()->with('error', 'Gagal terhubung ke mesin fingerprint.');
            }

            // Get attendance data from machine
            $attendanceData = $zk->getAttendance();
            $zk->disconnect();

            $syncedCount = 0;
            $errors = [];

            foreach ($attendanceData as $data) {
                try {
                    // Find employee by employee_id (UID from machine)
                    $employee = Employee::where('employee_id', $data['id'])->first();
                    
                    if (!$employee) {
                        $errors[] = "Employee ID {$data['id']} tidak ditemukan";
                        continue;
                    }

                    $timestamp = Carbon::parse($data['timestamp']);
                    $date = $timestamp->format('Y-m-d');
                    $time = $timestamp->format('H:i:s');

                    // Check if attendance record exists for this date
                    $attendance = Attendance::where('employee_id', $employee->id)
                                           ->where('date', $date)
                                           ->first();

                    if ($attendance) {
                        // Update check_out if time is later
                        if (!$attendance->check_out || $time > $attendance->check_out) {
                            $attendance->check_out = $time;
                            $attendance->attendance_machine_id = $machine->id;
                            
                            // Set keterangan berdasarkan jam pulang
                            $checkoutNotes = $this->determineCheckoutNotes($time);
                            if ($checkoutNotes) {
                                $attendance->notes = $checkoutNotes;
                            }
                            
                            $attendance->save();
                        }
                    } else {
                        // Create new attendance record
                        Attendance::create([
                            'employee_id' => $employee->id,
                            'attendance_machine_id' => $machine->id,
                            'date' => $date,
                            'check_in' => $time,
                            'status' => $this->determineStatus($time),
                        ]);
                    }

                    $syncedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error processing record: " . $e->getMessage();
                }
            }

            $message = "Berhasil sinkronisasi {$syncedCount} data absensi.";
            if (count($errors) > 0) {
                $message .= " Dengan " . count($errors) . " error.";
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Sync from all active machines
     */
    public function syncFromAllMachines()
    {
        $machines = AttendanceMachine::active()->get();
        $totalSynced = 0;

        foreach ($machines as $machine) {
            try {
                $zk = new \Rats\Zkteco\Lib\ZKTeco($machine->ip_address, $machine->port);
                
                if (!$zk->connect()) {
                    continue;
                }

                $attendanceData = $zk->getAttendance();
                $zk->disconnect();

                foreach ($attendanceData as $data) {
                    try {
                        $employee = Employee::where('employee_id', $data['id'])->first();
                        
                        if (!$employee) continue;

                        $timestamp = Carbon::parse($data['timestamp']);
                        $date = $timestamp->format('Y-m-d');
                        $time = $timestamp->format('H:i:s');

                        $attendance = Attendance::where('employee_id', $employee->id)
                                               ->where('date', $date)
                                               ->first();

                        if ($attendance) {
                            if (!$attendance->check_out || $time > $attendance->check_out) {
                                $attendance->check_out = $time;
                                $attendance->attendance_machine_id = $machine->id;
                                
                                // Set keterangan berdasarkan jam pulang
                                $checkoutNotes = $this->determineCheckoutNotes($time);
                                if ($checkoutNotes) {
                                    $attendance->notes = $checkoutNotes;
                                }
                                
                                $attendance->save();
                            }
                        } else {
                            Attendance::create([
                                'employee_id' => $employee->id,
                                'attendance_machine_id' => $machine->id,
                                'date' => $date,
                                'check_in' => $time,
                                'status' => $this->determineStatus($time),
                            ]);
                        }

                        $totalSynced++;
                    } catch (\Exception $e) {
                        \Log::error("Error syncing attendance: " . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Error connecting to machine {$machine->id}: " . $e->getMessage());
            }
        }

        return back()->with('success', "Berhasil sinkronisasi {$totalSynced} data absensi dari semua mesin.");
    }

    /**
     * Determine attendance status based on check-in time
     */
    private function determineStatus($checkInTime)
    {
        $checkIn = Carbon::parse($checkInTime);
        $standardTime = Carbon::parse('08:00:00'); // Jam masuk standar

        if ($checkIn->greaterThan($standardTime)) {
            return 'late';
        }

        return 'present';
    }

    /**
     * Determine checkout notes based on check-out time
     * Jam pulang standar: 16:00
     * - Sebelum 16:00 = Pulang Awal
     * - Setelah 16:00 = Lembur X jam Y menit
     */
    private function determineCheckoutNotes($checkOutTime)
    {
        $checkOut = Carbon::parse($checkOutTime);
        $standardCheckout = Carbon::parse('16:00:00');

        if ($checkOut->lessThan($standardCheckout)) {
            return 'Pulang Awal';
        } elseif ($checkOut->greaterThan($standardCheckout)) {
            $diff = $checkOut->diff($standardCheckout);
            $hours = $diff->h;
            $minutes = $diff->i;
            
            if ($hours > 0 && $minutes > 0) {
                return "Lembur {$hours} jam {$minutes} menit";
            } elseif ($hours > 0) {
                return "Lembur {$hours} jam";
            } else {
                return "Lembur {$minutes} menit";
            }
        }

        return null; // Tepat waktu
    }

    /**
     * Show import form
     */
    public function showImport()
    {
        return view('attendances.import');
    }

    /**
     * Process import from file
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:dat,txt,csv,xlsx,xls|max:10240', // max 10MB
            'format' => 'required|in:auto,zkteco,csv,excel',
        ]);

        try {
            $file = $request->file('file');
            $format = $request->input('format');
            $createEmployee = $request->boolean('create_employee', true);

            $imported = 0;
            $skipped = 0;
            $errors = [];

            // Read file content
            $content = file_get_contents($file->getRealPath());
            
            // Detect format if auto
            if ($format === 'auto') {
                $format = $this->detectFormat($file->getClientOriginalExtension(), $content);
            }

            // Parse based on format
            switch ($format) {
                case 'zkteco':
                    $records = $this->parseZKTecoFormat($content);
                    break;
                case 'csv':
                    $records = $this->parseCsvFormat($content);
                    break;
                case 'excel':
                    $records = $this->parseExcelFormat($file);
                    break;
                default:
                    throw new \Exception('Format tidak didukung');
            }

            // Process records
            foreach ($records as $index => $record) {
                try {
                    $result = $this->importRecord($record, $createEmployee);
                    if ($result) {
                        $imported++;
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Baris " . ($index + 1) . ": " . $e->getMessage();
                    $skipped++;
                }
            }

            $message = "Import selesai: {$imported} data berhasil";
            if ($skipped > 0) {
                $message .= ", {$skipped} data dilewati";
            }

            if (count($errors) > 0) {
                $message .= ". Errors: " . implode('; ', array_slice($errors, 0, 5));
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Detect file format
     */
    private function detectFormat($extension, $content)
    {
        $extension = strtolower($extension);
        
        if (in_array($extension, ['xlsx', 'xls'])) {
            return 'excel';
        }
        
        if ($extension === 'csv') {
            return 'csv';
        }
        
        // Check if ZKTeco format (tab separated, 4 columns)
        $lines = explode("\n", trim($content));
        if (count($lines) > 0) {
            $firstLine = trim($lines[0]);
            $parts = preg_split('/\s+/', $firstLine);
            if (count($parts) >= 3) {
                return 'zkteco';
            }
        }
        
        return 'csv';
    }

    /**
     * Parse ZKTeco DAT format
     */
    private function parseZKTecoFormat($content)
    {
        $records = [];
        $lines = explode("\n", trim($content));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Format: UserID  DateTime  DeviceID  Status
            // Example: 1	2025-12-11 08:00:00	1	0
            $parts = preg_split('/\s+/', $line);
            
            if (count($parts) >= 2) {
                $records[] = [
                    'employee_id' => $parts[0],
                    'datetime' => $parts[1] . (isset($parts[2]) && !is_numeric($parts[2]) ? ' ' . $parts[2] : ''),
                    'status' => isset($parts[3]) ? $parts[3] : 0,
                ];
            }
        }
        
        return $records;
    }

    /**
     * Parse CSV format
     */
    private function parseCsvFormat($content)
    {
        $records = [];
        $lines = explode("\n", trim($content));
        $headers = null;
        
        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $parts = str_getcsv($line);
            
            if ($index === 0 && !is_numeric($parts[0])) {
                // First line is header
                $headers = array_map('strtolower', $parts);
                continue;
            }
            
            if ($headers) {
                $record = array_combine($headers, $parts);
            } else {
                $record = [
                    'employee_id' => $parts[0],
                    'datetime' => $parts[1],
                    'type' => isset($parts[2]) ? $parts[2] : 'in',
                ];
            }
            
            $records[] = $record;
        }
        
        return $records;
    }

    /**
     * Parse Excel format
     */
    private function parseExcelFormat($file)
    {
        // Requires PhpSpreadsheet
        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            throw new \Exception('PhpSpreadsheet tidak terinstall. Gunakan format CSV atau DAT.');
        }
        
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        $records = [];
        $headers = null;
        
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                $headers = array_map('strtolower', $row);
                continue;
            }
            
            if (count(array_filter($row)) === 0) continue;
            
            $record = array_combine($headers, $row);
            $records[] = $record;
        }
        
        return $records;
    }

    /**
     * Import single record
     */
    private function importRecord($record, $createEmployee = true)
    {
        // Extract employee ID
        $employeeId = $record['employee_id'] ?? $record['userid'] ?? $record['user_id'] ?? null;
        
        if (!$employeeId) {
            throw new \Exception('Employee ID tidak ditemukan');
        }

        // Find or create employee
        $employee = Employee::where('employee_id', $employeeId)->first();
        
        if (!$employee) {
            if (!$createEmployee) {
                throw new \Exception("Karyawan {$employeeId} tidak ditemukan");
            }
            
            $employee = Employee::create([
                'employee_id' => $employeeId,
                'name' => $record['name'] ?? $record['nama'] ?? "Employee {$employeeId}",
                'is_active' => true,
            ]);
        }

        // Parse datetime
        $datetime = $record['datetime'] ?? $record['date'] . ' ' . $record['time'] ?? null;
        
        if (!$datetime) {
            throw new \Exception('DateTime tidak valid');
        }

        $carbonTime = Carbon::parse($datetime);
        $date = $carbonTime->format('Y-m-d');
        $time = $carbonTime->format('H:i:s');

        // Check existing attendance
        $attendance = Attendance::where('employee_id', $employee->id)
                                ->where('date', $date)
                                ->first();

        if ($attendance) {
            // Update check_out if time is later
            if (!$attendance->check_out || $time > $attendance->check_in) {
                $attendance->check_out = $time;
                
                // Set keterangan berdasarkan jam pulang
                $checkoutNotes = $this->determineCheckoutNotes($time);
                if ($checkoutNotes) {
                    $attendance->notes = $checkoutNotes;
                }
                
                $attendance->save();
                return true;
            }
            return false; // Skip duplicate
        } else {
            // Create new attendance
            Attendance::create([
                'employee_id' => $employee->id,
                'attendance_machine_id' => null,
                'date' => $date,
                'check_in' => $time,
                'status' => $this->determineStatus($time),
            ]);
            return true;
        }
    }

    /**
     * Export attendance to Excel
     */
    public function export(Request $request)
    {
        // TODO: Implement Excel export
        return back()->with('info', 'Fitur export sedang dalam pengembangan.');
    }

    /**
     * Recalculate notes for existing attendance records
     * Update keterangan (pulang awal/lembur) untuk data yang sudah ada
     */
    public function recalculateNotes()
    {
        try {
            // Ambil semua data attendance yang punya check_out
            $attendances = Attendance::whereNotNull('check_out')->get();

            $updated = 0;

            foreach ($attendances as $attendance) {
                $oldNotes = $attendance->notes;
                $checkoutNotes = $this->determineCheckoutNotes($attendance->check_out);
                
                // Update notes (bisa null untuk tepat waktu, atau string untuk Pulang Awal/Lembur)
                $attendance->notes = $checkoutNotes;
                $attendance->save();
                $updated++;
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil recalculate {$updated} keterangan absensi.",
                'updated' => $updated
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}

