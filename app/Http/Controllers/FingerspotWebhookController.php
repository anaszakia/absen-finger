<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\AttendanceMachine;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FingerspotWebhookController extends Controller
{
    /**
     * Receive webhook from Fingerspot.io
     * Endpoint untuk menerima data dari developer.fingerspot.io
     */
    public function receive(Request $request)
    {
        // Log raw data untuk debugging
        $rawData = file_get_contents('php://input');
        
        Log::info('Fingerspot Webhook Received', [
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'raw_data' => $rawData,
        ]);

        try {
            $data = json_decode($rawData, true);
            
            if (!$data) {
                $data = $request->all();
            }

            Log::info('Fingerspot Parsed Data', ['data' => $data]);

            // Validasi data dari fingerspot
            if (!isset($data['type']) || !isset($data['cloud_id'])) {
                Log::warning('Invalid fingerspot webhook data', ['data' => $data]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook data'
                ], 400);
            }

            $type = $data['type'];
            $cloudId = $data['cloud_id'];

            // Simpan log webhook
            $this->saveWebhookLog($cloudId, $type, $data);

            // Process berdasarkan type
            $result = $this->processWebhookData($type, $cloudId, $data);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
                'type' => $type,
                'processed' => $result,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Fingerspot Webhook Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing webhook',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process webhook data based on type
     */
    private function processWebhookData($type, $cloudId, $data)
    {
        // Cari atau buat mesin berdasarkan cloud_id
        $machine = AttendanceMachine::where('serial_number', $cloudId)->first();
        
        if (!$machine) {
            // Auto-create machine jika belum ada
            $machine = AttendanceMachine::create([
                'name' => $data['device_name'] ?? 'Mesin ' . substr($cloudId, 0, 8),
                'serial_number' => $cloudId,
                'ip_address' => $data['device_ip'] ?? '0.0.0.0',
                'port' => 9003,
                'location' => 'Auto-registered from Fingerspot',
                'is_active' => true,
            ]);
            
            Log::info('Auto-created machine from fingerspot', [
                'cloud_id' => $cloudId,
                'machine_id' => $machine->id,
            ]);
        }

        switch ($type) {
            case 'attendance':
            case 'scanlog':
                return $this->processAttendance($data, $machine);
            
            case 'user':
            case 'person':
                return $this->processUser($data, $machine);
            
            default:
                Log::info('Unknown webhook type', ['type' => $type, 'data' => $data]);
                return ['message' => 'Type processed but no action taken'];
        }
    }

    /**
     * Process attendance/scanlog data
     */
    private function processAttendance($data, $machine)
    {
        $processed = 0;

        // Format data fingerspot untuk attendance
        // Biasanya ada field: pin, scan_date, personname, dll
        
        if (isset($data['data']) && is_array($data['data'])) {
            // Multiple records
            foreach ($data['data'] as $record) {
                if ($this->saveAttendanceFromFingerspot($record, $machine)) {
                    $processed++;
                }
            }
        } else {
            // Single record
            if ($this->saveAttendanceFromFingerspot($data, $machine)) {
                $processed = 1;
            }
        }

        return ['attendances' => $processed];
    }

    /**
     * Save attendance from fingerspot data
     */
    private function saveAttendanceFromFingerspot($data, $machine)
    {
        // Extract employee ID (bisa berupa pin, person_id, atau scan_id)
        $employeeId = $data['pin'] ?? $data['person_id'] ?? $data['scan_id'] ?? $data['user_id'] ?? null;
        
        if (!$employeeId) {
            Log::warning('No employee ID in fingerspot data', ['data' => $data]);
            return false;
        }

        // Find or create employee
        $employee = Employee::where('employee_id', $employeeId)
                           ->orWhere('pin', $employeeId)
                           ->first();
        
        if (!$employee) {
            // Auto-create employee
            $employee = Employee::create([
                'employee_id' => $employeeId,
                'pin' => $employeeId,
                'name' => $data['personname'] ?? $data['name'] ?? "Employee {$employeeId}",
                'is_active' => true,
            ]);
            
            Log::info('Auto-created employee from fingerspot', [
                'employee_id' => $employeeId,
                'pin' => $employeeId,
                'name' => $employee->name,
            ]);
        }

        // Parse datetime - fingerspot biasanya format: scan_date
        $scanDate = $data['scan_date'] ?? $data['datetime'] ?? $data['timestamp'] ?? now();
        $carbonTime = Carbon::parse($scanDate);
        $date = $carbonTime->format('Y-m-d');
        $time = $carbonTime->format('H:i:s');

        // Check existing attendance
        $attendance = Attendance::where('employee_id', $employee->id)
                                ->where('date', $date)
                                ->first();

        if ($attendance) {
            // Update check_out jika waktu lebih baru dari check_in
            if (!$attendance->check_out || $time > $attendance->check_in) {
                $attendance->check_out = $time;
                $attendance->attendance_machine_id = $machine->id;
                $attendance->save();
                
                Log::info('Updated check-out from fingerspot', [
                    'employee' => $employee->name,
                    'date' => $date,
                    'check_out' => $time,
                ]);
                
                return true;
            }
            return false; // Skip duplicate
        } else {
            // Create new attendance
            Attendance::create([
                'employee_id' => $employee->id,
                'attendance_machine_id' => $machine->id,
                'date' => $date,
                'check_in' => $time,
                'status' => $this->determineStatus($time),
            ]);
            
            Log::info('Created attendance from fingerspot', [
                'employee' => $employee->name,
                'date' => $date,
                'check_in' => $time,
            ]);
            
            return true;
        }
    }

    /**
     * Process user data
     */
    private function processUser($data, $machine)
    {
        $processed = 0;

        if (isset($data['data']) && is_array($data['data'])) {
            // Multiple users
            foreach ($data['data'] as $record) {
                if ($this->saveUserFromFingerspot($record)) {
                    $processed++;
                }
            }
        } else {
            // Single user
            if ($this->saveUserFromFingerspot($data)) {
                $processed = 1;
            }
        }

        return ['users' => $processed];
    }

    /**
     * Save user from fingerspot data
     */
    private function saveUserFromFingerspot($data)
    {
        $employeeId = $data['pin'] ?? $data['person_id'] ?? $data['id'] ?? null;
        
        if (!$employeeId) {
            return false;
        }

        $employee = Employee::where('employee_id', $employeeId)
                           ->orWhere('pin', $employeeId)
                           ->first();
        
        if ($employee) {
            // Update existing
            $updated = false;
            
            if (isset($data['personname']) && $employee->name !== $data['personname']) {
                $employee->name = $data['personname'];
                $updated = true;
            }
            
            if (empty($employee->pin) && !empty($employeeId)) {
                $employee->pin = $employeeId;
                $updated = true;
            }
            
            if ($updated) {
                $employee->save();
                Log::info('Updated employee from fingerspot', [
                    'employee_id' => $employeeId,
                ]);
            }
            
            return $updated;
        } else {
            // Create new
            Employee::create([
                'employee_id' => $employeeId,
                'pin' => $employeeId,
                'name' => $data['personname'] ?? $data['name'] ?? "Employee {$employeeId}",
                'is_active' => true,
            ]);
            
            Log::info('Created employee from fingerspot', [
                'employee_id' => $employeeId,
                'pin' => $employeeId,
            ]);
            
            return true;
        }
    }

    /**
     * Determine status based on check-in time
     */
    private function determineStatus($checkInTime)
    {
        $checkIn = Carbon::parse($checkInTime);
        $standardTime = Carbon::parse('08:00:00');

        return $checkIn->greaterThan($standardTime) ? 'late' : 'present';
    }

    /**
     * Save webhook log to database
     */
    private function saveWebhookLog($cloudId, $type, $data)
    {
        try {
            \DB::table('fingerspot_webhook_logs')->insert([
                'cloud_id' => $cloudId,
                'type' => $type,
                'data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Table mungkin belum ada, skip
            Log::warning('Failed to save webhook log', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Test endpoint
     */
    public function test(Request $request)
    {
        Log::info('Fingerspot webhook test accessed', [
            'ip' => $request->ip(),
            'time' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fingerspot webhook endpoint is active',
            'server_time' => now()->toDateTimeString(),
            'server_url' => url('/api/fingerspot/webhook'),
        ]);
    }

    /**
     * Check real connection to Fingerspot.io API
     */
    public function checkConnection()
    {
        try {
            $cloudId = env('FINGERSPOT_CLOUD_ID', 'C263045107E1C26');
            $apiToken = env('FINGERSPOT_API_TOKEN');
            
            if (!$apiToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Token belum dikonfigurasi. Login ke developer.fingerspot.io untuk mendapatkan API Token, lalu set FINGERSPOT_API_TOKEN di file .env',
                    'connected' => false,
                    'instructions' => 'Edit file .env, tambahkan: FINGERSPOT_API_TOKEN=your_api_token_here',
                ]);
            }

            // Test dengan API get_attlog untuk memastikan koneksi ke mesin real
            $url = "https://developer.fingerspot.io/api/get_attlog";
            
            // Generate unique trans_id
            $transId = 'check_' . time();
            
            // Get data dari 2 hari terakhir (maksimal range yang diperbolehkan)
            $endDate = now();
            $startDate = now()->subDays(1);
            
            $postData = [
                'trans_id' => $transId,
                'cloud_id' => $cloudId,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiToken,
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                return response()->json([
                    'success' => false,
                    'message' => 'CURL Error: ' . $error,
                    'connected' => false,
                ]);
            }
            
            $responseData = json_decode($response, true);
            
            // Check if response is successful
            if ($httpCode === 200) {
                return response()->json([
                    'success' => true,
                    'message' => 'Koneksi ke mesin berhasil! Mesin Revo W-230N terhubung ke cloud Fingerspot.io',
                    'connected' => true,
                    'cloud_id' => $cloudId,
                    'server' => 'FDEVICE.COM:9003',
                    'device' => 'Revo W-230N',
                    'data_count' => isset($responseData['data']) ? count($responseData['data']) : 0,
                    'message_detail' => isset($responseData['message']) ? $responseData['message'] : 'API responded successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal terhubung ke API Fingerspot.io (HTTP ' . $httpCode . '). Cek API Token Anda.',
                    'connected' => false,
                    'http_code' => $httpCode,
                    'response' => $responseData,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'connected' => false,
            ]);
        }
    }

    /**
     * Sync users from Fingerspot.io machine to database
     * Note: Fingerspot.io tidak menyediakan endpoint untuk pull user secara langsung
     * Data user akan dikirim otomatis via webhook saat:
     * 1. Ada user baru didaftarkan di mesin
     * 2. User melakukan absensi pertama kali
     * 3. Webhook "User/Person" event di-trigger
     */
    public function syncUsers()
    {
        try {
            $cloudId = env('FINGERSPOT_CLOUD_ID', 'C263045107E1C26');
            $apiToken = env('FINGERSPOT_API_TOKEN');
            
            if (!$apiToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Token belum dikonfigurasi. Set FINGERSPOT_API_TOKEN di file .env',
                ]);
            }

            // Gunakan API get_attlog untuk mendapatkan data scan dari mesin (2 hari terakhir maksimal)
            $url = "https://developer.fingerspot.io/api/get_attlog";
            
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-2 days')); // Maksimal 2 hari sesuai dokumentasi
            
            $postData = [
                'trans_id' => "1", // ID perintah unik (string number)
                'cloud_id' => $cloudId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
            
            Log::info('Requesting attlog from Fingerspot', $postData);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiToken,
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                Log::error('CURL Error on get_attlog', ['error' => $error]);
                return response()->json([
                    'success' => false,
                    'message' => 'Connection error: ' . $error,
                ]);
            }
            
            $responseData = json_decode($response, true);
            
            Log::info('Attlog response', [
                'http_code' => $httpCode,
                'response' => $responseData,
            ]);
            
            if ($httpCode !== 200) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Error (HTTP ' . $httpCode . '): ' . ($responseData['message'] ?? 'Unknown error'),
                    'http_code' => $httpCode,
                    'response' => $responseData,
                ]);
            }
            
            // Check jika ada error dari API
            if (isset($responseData['error_code'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fingerspot API Error: ' . ($responseData['message'] ?? $responseData['error_code']),
                    'error_code' => $responseData['error_code'],
                    'hint' => 'Pastikan Cloud ID benar dan terdaftar di akun API Token Anda',
                ]);
            }
            
            // Process attendance data
            $usersSynced = 0;
            $attendancesSynced = 0;
            $uniqueUsers = [];
            
            if (isset($responseData['data']) && is_array($responseData['data'])) {
                $machine = AttendanceMachine::where('serial_number', $cloudId)->first();
                
                if (!$machine) {
                    $machine = AttendanceMachine::create([
                        'name' => 'Revo W-230N',
                        'serial_number' => $cloudId,
                        'ip_address' => 'FDEVICE.COM',
                        'port' => 9003,
                        'location' => 'Auto-registered from Fingerspot.io',
                        'is_active' => true,
                    ]);
                }
                
                foreach ($responseData['data'] as $record) {
                    $pin = $record['pin'] ?? $record['personid'] ?? null;
                    $name = $record['personname'] ?? $record['name'] ?? null;
                    $scanDate = $record['scan_date'] ?? $record['datetime'] ?? null;
                    
                    if (!$pin || !$scanDate) continue;
                    
                    // Track unique users
                    if (!isset($uniqueUsers[$pin])) {
                        $employee = Employee::updateOrCreate(
                            ['employee_id' => $pin],
                            [
                                'name' => $name ?? "Employee $pin",
                                'is_active' => true,
                            ]
                        );
                        
                        $uniqueUsers[$pin] = $employee;
                        $usersSynced++;
                    } else {
                        $employee = $uniqueUsers[$pin];
                    }
                    
                    // Save attendance
                    $carbonTime = Carbon::parse($scanDate);
                    $date = $carbonTime->format('Y-m-d');
                    $time = $carbonTime->format('H:i:s');
                    
                    $attendance = Attendance::where('employee_id', $employee->id)
                                          ->where('date', $date)
                                          ->first();
                    
                    if (!$attendance) {
                        Attendance::create([
                            'employee_id' => $employee->id,
                            'attendance_machine_id' => $machine->id,
                            'date' => $date,
                            'check_in' => $time,
                            'status' => $carbonTime->format('H:i') > '08:00' ? 'late' : 'present',
                        ]);
                        $attendancesSynced++;
                    } elseif (!$attendance->check_out && $time > $attendance->check_in) {
                        $attendance->check_out = $time;
                        $attendance->save();
                        $attendancesSynced++;
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil sync {$usersSynced} user dan {$attendancesSynced} data absensi dari mesin",
                'synced' => $usersSynced,
                'users' => $usersSynced,
                'attendances' => $attendancesSynced,
                'date_range' => "$startDate sampai $endDate",
            ]);
            
        } catch (\Exception $e) {
            Log::error('Sync users error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get list of employees from database (yang sudah masuk via webhook)
     */
    public function getEmployeesFromWebhook()
    {
        try {
            $employees = Employee::orderBy('created_at', 'desc')
                ->take(50)
                ->get(['id', 'employee_id', 'name', 'created_at']);
            
            return response()->json([
                'success' => true,
                'message' => 'Data karyawan yang sudah tersimpan dari webhook',
                'total' => Employee::count(),
                'employees' => $employees,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
}
