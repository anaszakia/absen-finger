<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\AttendanceMachine;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendancePushController extends Controller
{
    /**
     * Receive attendance data pushed from fingerprint machine
     * This endpoint accepts data from ZKTeco devices configured to push to server
     */
    public function receive(Request $request)
    {
        // Log semua data yang diterima untuk debugging
        Log::info('Push Data Received', [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'all_data' => $request->all(),
            'raw_input' => substr($request->getContent(), 0, 500),
        ]);

        try {
            // Parse data yang masuk
            $data = $request->all();
            
            // Format JSON body
            if (empty($data) && $request->getContent()) {
                $data = json_decode($request->getContent(), true);
            }

            // Format Raw data dari mesin ZKTeco
            if (empty($data)) {
                $rawData = $request->getContent();
                Log::warning('Received raw data format', ['raw' => bin2hex(substr($rawData, 0, 100))]);
            }

            Log::info('Parsed data', ['data' => $data]);

            // Identifikasi mesin berdasarkan IP
            $machine = AttendanceMachine::where('ip_address', $request->ip())->first();
            
            if (!$machine) {
                Log::warning('Unknown machine IP', ['ip' => $request->ip()]);
            }

            // Deteksi tipe data: User/Employee atau Attendance
            $result = $this->processData($data, $machine, $request);

            return response()->json([
                'success' => true,
                'message' => 'Data received successfully',
                'processed' => $result,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error processing push data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process incoming data - detect type and route accordingly
     */
    private function processData($data, $machine = null, $request = null)
    {
        $result = [
            'employees' => 0,
            'attendances' => 0,
        ];

        // Deteksi tipe data
        if ($this->isEmployeeData($data)) {
            $result['employees'] = $this->processEmployeeData($data, $machine);
        } elseif ($this->isAttendanceData($data)) {
            $result['attendances'] = $this->processAttendanceData($data, $machine);
        } else {
            // Coba kedua-duanya
            Log::info('Unknown data format, trying both processors');
            $result['employees'] = $this->processEmployeeData($data, $machine);
            $result['attendances'] = $this->processAttendanceData($data, $machine);
        }

        return $result;
    }

    /**
     * Check if data is employee/user data
     */
    private function isEmployeeData($data)
    {
        // Employee data biasanya punya: name, privilege, password, cardNumber
        $employeeKeys = ['name', 'privilege', 'cardNumber', 'enrollData', 'fingerprint'];
        
        foreach ($employeeKeys as $key) {
            if (isset($data[$key])) {
                return true;
            }
        }

        // Check if it's array of employees
        if (isset($data['users']) || isset($data['employees'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if data is attendance data
     */
    private function isAttendanceData($data)
    {
        // Attendance data biasanya punya: timestamp, datetime, checktime
        $attendanceKeys = ['timestamp', 'datetime', 'checktime', 'punch', 'state'];
        
        foreach ($attendanceKeys as $key) {
            if (isset($data[$key])) {
                return true;
            }
        }

        // Check if it's array of attendance records
        if (isset($data['records']) || isset($data['attendances'])) {
            return true;
        }

        return false;
    }

    /**
     * Process employee/user data from machine
     */
    private function processEmployeeData($data, $machine = null)
    {
        $processed = 0;

        // Single employee
        if (isset($data['userID']) || isset($data['uid']) || isset($data['PIN'])) {
            $this->saveEmployee($data, $machine);
            $processed = 1;
        }
        // Multiple employees
        elseif (isset($data['users']) && is_array($data['users'])) {
            foreach ($data['users'] as $user) {
                $this->saveEmployee($user, $machine);
                $processed++;
            }
        }
        elseif (isset($data['employees']) && is_array($data['employees'])) {
            foreach ($data['employees'] as $employee) {
                $this->saveEmployee($employee, $machine);
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Save employee data from machine to database
     */
    private function saveEmployee($data, $machine = null)
    {
        // Extract employee ID
        $employeeId = $data['userID'] ?? $data['uid'] ?? $data['PIN'] ?? $data['employee_id'] ?? null;
        
        if (!$employeeId) {
            Log::warning('No employee ID in user data', ['data' => $data]);
            return;
        }

        // Extract name
        $name = $data['name'] ?? $data['username'] ?? 'Employee ' . $employeeId;

        // Check if employee already exists
        $employee = Employee::where('employee_id', $employeeId)->first();

        if ($employee) {
            // Update existing employee
            $updated = false;
            
            if (isset($data['name']) && $employee->name !== $data['name']) {
                $employee->name = $data['name'];
                $updated = true;
            }

            if ($updated) {
                $employee->save();
                Log::info('Employee updated from machine', [
                    'employee_id' => $employeeId,
                    'name' => $name,
                ]);
            }
        } else {
            // Create new employee
            $employee = Employee::create([
                'employee_id' => $employeeId,
                'name' => $name,
                'is_active' => true,
            ]);

            Log::info('New employee created from machine', [
                'employee_id' => $employeeId,
                'name' => $name,
            ]);
        }

        return $employee;
    }

    /**
     * Process attendance data
     */
    private function processAttendanceData($data, $machine = null)
    {
        $processed = 0;

        // Format data yang mungkin diterima dari mesin:
        // - userID: ID karyawan
        // - timestamp: waktu absensi
        // - type: check-in/check-out
        // - verifyMode: cara verifikasi (fingerprint, card, password)

        if (isset($data['userID']) && isset($data['timestamp'])) {
            // Single record
            $this->saveAttendance($data, $machine);
            $processed = 1;
        } elseif (isset($data['records']) && is_array($data['records'])) {
            // Multiple records
            foreach ($data['records'] as $record) {
                $this->saveAttendance($record, $machine);
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Save single attendance record
     */
    private function saveAttendance($data, $machine = null)
    {
        // Cari employee berdasarkan userID/employeeID
        $employeeId = $data['userID'] ?? $data['employee_id'] ?? $data['uid'] ?? null;
        
        if (!$employeeId) {
            Log::warning('No employee ID in attendance data', ['data' => $data]);
            return;
        }

        $employee = Employee::where('employee_id', $employeeId)->first();
        
        if (!$employee) {
            Log::warning('Employee not found', ['employee_id' => $employeeId]);
            return;
        }

        // Parse timestamp
        $timestamp = $data['timestamp'] ?? $data['datetime'] ?? now();
        $carbonTime = Carbon::parse($timestamp);
        $date = $carbonTime->format('Y-m-d');
        $time = $carbonTime->format('H:i:s');

        // Check apakah sudah ada record untuk hari ini
        $attendance = Attendance::where('employee_id', $employee->id)
                                ->where('date', $date)
                                ->first();

        if ($attendance) {
            // Update check_out jika waktu lebih baru
            if (!$attendance->check_out || $time > $attendance->check_out) {
                $attendance->check_out = $time;
                if ($machine) {
                    $attendance->attendance_machine_id = $machine->id;
                }
                $attendance->save();
                
                Log::info('Updated check-out', [
                    'employee' => $employee->name,
                    'date' => $date,
                    'time' => $time,
                ]);
            }
        } else {
            // Buat record baru
            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'attendance_machine_id' => $machine ? $machine->id : null,
                'date' => $date,
                'check_in' => $time,
                'status' => $this->determineStatus($time),
            ]);
            
            Log::info('Created new attendance', [
                'employee' => $employee->name,
                'date' => $date,
                'time' => $time,
            ]);
        }
    }

    /**
     * Determine attendance status based on check-in time
     */
    private function determineStatus($checkInTime)
    {
        $checkIn = Carbon::parse($checkInTime);
        $standardTime = Carbon::parse('08:00:00');

        if ($checkIn->greaterThan($standardTime)) {
            return 'late';
        }

        return 'present';
    }

    /**
     * Test endpoint untuk verifikasi server aktif
     */
    public function test(Request $request)
    {
        Log::info('Test endpoint accessed', [
            'ip' => $request->ip(),
            'time' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Push server is active and ready to receive data',
            'server_time' => now()->toDateTimeString(),
            'server_ip' => $request->server('SERVER_ADDR'),
            'client_ip' => $request->ip(),
        ]);
    }

    /**
     * View log untuk melihat data yang masuk
     */
    public function viewLogs()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return response()->json([
                'success' => false,
                'message' => 'Log file not found',
            ], 404);
        }

        // Ambil 50 baris terakhir
        $lines = [];
        $file = new \SplFileObject($logFile, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();
        $startLine = max(0, $lastLine - 100);
        
        $file->seek($startLine);
        while (!$file->eof()) {
            $lines[] = $file->fgets();
        }

        return response()->json([
            'success' => true,
            'logs' => implode('', array_slice($lines, -50)),
        ]);
    }
}
