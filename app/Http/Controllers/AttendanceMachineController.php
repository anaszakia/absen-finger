<?php

namespace App\Http\Controllers;

use App\Models\AttendanceMachine;
use Illuminate\Http\Request;

class AttendanceMachineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $machines = AttendanceMachine::orderBy('created_at', 'desc')->get();
        return view('machines.index', compact('machines'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('machines.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'serial_number' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $machine = AttendanceMachine::create($validated);

        return redirect()->route('machines.index')
            ->with('success', 'Mesin absensi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AttendanceMachine $machine)
    {
        return view('machines.show', compact('machine'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AttendanceMachine $machine)
    {
        return view('machines.edit', compact('machine'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AttendanceMachine $machine)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'serial_number' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $machine->update($validated);

        return redirect()->route('machines.index')
            ->with('success', 'Mesin absensi berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttendanceMachine $machine)
    {
        $machine->delete();

        return redirect()->route('machines.index')
            ->with('success', 'Mesin absensi berhasil dihapus.');
    }

    /**
     * Test connection to the machine
     */
    public function testConnection(AttendanceMachine $machine)
    {
        try {
            \Log::info("Testing connection to {$machine->ip_address}:{$machine->port}");
            
            // Set timeout untuk koneksi
            $timeout = 10; // 10 detik
            $zk = new \Rats\Zkteco\Lib\ZKTeco($machine->ip_address, $machine->port, $timeout);
            
            $connected = $zk->connect();
            \Log::info("Connection result: " . ($connected ? 'Success' : 'Failed'));
            
            if ($connected) {
                try {
                    $version = $zk->version();
                    $platform = $zk->platform();
                    $serialNumber = $zk->serialNumber();
                    $deviceName = $zk->deviceName();
                    $zk->disconnect();

                    return response()->json([
                        'success' => true,
                        'message' => 'Koneksi berhasil!',
                        'data' => [
                            'version' => $version,
                            'platform' => $platform,
                            'serial_number' => $serialNumber,
                            'device_name' => $deviceName,
                        ]
                    ]);
                } catch (\Exception $e) {
                    $zk->disconnect();
                    throw $e;
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal terhubung ke mesin. Pastikan IP dan Port sudah benar, dan mesin dalam keadaan aktif.'
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error("Connection error: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'details' => [
                    'ip' => $machine->ip_address,
                    'port' => $machine->port,
                    'error_type' => get_class($e)
                ]
            ], 500);
        }
    }

    /**
     * Get device info
     */
    public function getDeviceInfo(AttendanceMachine $machine)
    {
        try {
            $zk = new \Rats\Zkteco\Lib\ZKTeco($machine->ip_address, $machine->port);
            
            if ($zk->connect()) {
                $info = [
                    'serial_number' => $zk->serialNumber(),
                    'device_name' => $zk->deviceName(),
                    'platform' => $zk->platform(),
                    'version' => $zk->version(),
                    'os_version' => $zk->osVersion(),
                    'work_code' => $zk->workCode(),
                ];
                $zk->disconnect();

                return response()->json([
                    'success' => true,
                    'data' => $info
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal terhubung ke mesin.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
