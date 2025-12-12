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
            // Cek berbagai kemungkinan field nama dari webhook fingerspot
            $name = $data['personname'] 
                 ?? $data['name'] 
                 ?? $data['person_name']
                 ?? $data['fullname']
                 ?? $data['full_name']
                 ?? $data['username']
                 ?? "Employee {$employeeId}";
            
            $employee = Employee::create([
                'employee_id' => $employeeId,
                'pin' => $employeeId,
                'name' => $name,
                'is_active' => true,
            ]);
            
            Log::info('Auto-created employee from fingerspot attendance', [
                'employee_id' => $employeeId,
                'pin' => $employeeId,
                'name' => $employee->name,
                'webhook_data' => $data,
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

        // Cek berbagai kemungkinan field nama dari webhook fingerspot
        $name = $data['personname'] 
             ?? $data['name'] 
             ?? $data['person_name']
             ?? $data['fullname']
             ?? $data['full_name']
             ?? $data['username']
             ?? null;
        
        $employee = Employee::where('employee_id', $employeeId)
                           ->orWhere('pin', $employeeId)
                           ->first();
        
        if ($employee) {
            // Update existing
            $updated = false;
            
            // Update nama jika ada di webhook dan berbeda dari database
            if ($name && $employee->name !== $name) {
                $oldName = $employee->name;
                $employee->name = $name;
                $updated = true;
                
                Log::info('Updated employee name from fingerspot webhook', [
                    'employee_id' => $employeeId,
                    'old_name' => $oldName,
                    'new_name' => $name,
                ]);
            }
            
            if (empty($employee->pin) && !empty($employeeId)) {
                $employee->pin = $employeeId;
                $updated = true;
            }
            
            if ($updated) {
                $employee->save();
            }
            
            return $updated;
        } else {
            // Create new
            $finalName = $name ?? "Employee {$employeeId}";
            
            Employee::create([
                'employee_id' => $employeeId,
                'pin' => $employeeId,
                'name' => $finalName,
                'is_active' => true,
            ]);
            
            Log::info('Created employee from fingerspot webhook', [
                'employee_id' => $employeeId,
                'pin' => $employeeId,
                'name' => $finalName,
                'webhook_data' => $data,
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
     * Check real connection to Fingerspot.io API and device status
     */
    public function checkConnection()
    {
        try {
            $cloudId = env('FINGERSPOT_CLOUD_ID');
            $apiToken = env('FINGERSPOT_API_TOKEN');
            
            if (!$apiToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Token belum dikonfigurasi. Login ke developer.fingerspot.io untuk mendapatkan API Token, lalu set FINGERSPOT_API_TOKEN di file .env',
                    'connected' => false,
                    'instructions' => 'Edit file .env, tambahkan: FINGERSPOT_API_TOKEN=your_api_token_here',
                ]);
            }

            if (!$cloudId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cloud ID belum dikonfigurasi. Set FINGERSPOT_CLOUD_ID di file .env',
                    'connected' => false,
                    'instructions' => 'Edit file .env, tambahkan: FINGERSPOT_CLOUD_ID=your_cloud_id',
                ]);
            }

            // Use get_attlog API to check connection - test dengan range 2 hari
            $url = "https://developer.fingerspot.io/api/get_attlog";
            
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-1 days'));
            
            $postData = [
                'trans_id' => "1",
                'cloud_id' => $cloudId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiToken
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            
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
            
            // Log the response for debugging
            \Log::info('Fingerspot API Response', [
                'http_code' => $httpCode,
                'response' => $responseData
            ]);
            
            // Check if API token is invalid
            if ($httpCode === 401) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Token tidak valid atau expired. Silakan generate token baru dari developer.fingerspot.io',
                    'connected' => false,
                    'http_code' => $httpCode,
                ]);
            }
            
            // Check if cloud_id not found
            if ($httpCode === 404) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cloud ID tidak ditemukan. Pastikan Cloud ID benar: ' . $cloudId,
                    'connected' => false,
                    'http_code' => $httpCode,
                ]);
            }
            
            // Check jika ada error dari API
            if (isset($responseData['error_code'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fingerspot API Error: ' . ($responseData['message'] ?? $responseData['error_code']),
                    'connected' => false,
                    'error_code' => $responseData['error_code'],
                ]);
            }
            
            // Check if response is successful
            if ($httpCode === 200 && isset($responseData['success']) && $responseData['success'] === true) {
                $dataCount = isset($responseData['data']) ? count($responseData['data']) : 0;
                
                return response()->json([
                    'success' => true,
                    'message' => 'Koneksi ke Fingerspot.io API berhasil! Mesin terhubung ke webhook.',
                    'connected' => true,
                    'cloud_id' => $cloudId,
                    'trans_id' => $responseData['trans_id'] ?? '1',
                    'data_count' => $dataCount,
                    'date_range' => "$startDate s/d $endDate",
                    'info' => 'Data absensi akan otomatis dikirim via webhook ke aplikasi ini',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal terhubung ke Fingerspot.io API (HTTP ' . $httpCode . ')',
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
            $cloudId = env('FINGERSPOT_CLOUD_ID');
            $apiToken = env('FINGERSPOT_API_TOKEN');
            
            if (!$apiToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Token belum dikonfigurasi. Set FINGERSPOT_API_TOKEN di file .env',
                ]);
            }

            if (!$cloudId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cloud ID belum dikonfigurasi. Set FINGERSPOT_CLOUD_ID di file .env',
                ]);
            }

            // Gunakan API get_attlog untuk mendapatkan data scan dari mesin (2 hari terakhir maksimal)
            $url = "https://developer.fingerspot.io/api/get_attlog";
            
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-1 days')); // 2 hari sesuai dokumentasi
            
            $postData = [
                'trans_id' => "1",
                'cloud_id' => $cloudId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
            
            Log::info('Requesting attlog from Fingerspot', $postData);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiToken
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
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
                        'name' => 'Fingerspot Device',
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

    /**
     * Get user info from Fingerspot.io for a specific employee
     */
    public function getUserInfo($pin)
    {
        try {
            $cloudId = env('FINGERSPOT_CLOUD_ID');
            $apiToken = env('FINGERSPOT_API_TOKEN');
            
            if (!$apiToken || !$cloudId) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Token atau Cloud ID belum dikonfigurasi',
                ]);
            }

            $url = "https://developer.fingerspot.io/api/get_userinfo";
            
            $postData = [
                'trans_id' => "1",
                'cloud_id' => $cloudId,
                'pin' => $pin,
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiToken
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                return response()->json([
                    'success' => false,
                    'message' => 'CURL Error: ' . $error,
                ]);
            }
            
            $responseData = json_decode($response, true);
            
            if ($httpCode === 200 && isset($responseData['success']) && $responseData['success'] === true) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil user info',
                    'http_code' => $httpCode,
                    'response' => $responseData,
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get all PINs from Fingerspot.io
     * Fungsi ini mengambil daftar semua PIN yang terdaftar di mesin
     */
    public function getAllPins()
    {
        try {
            $cloudId = env('FINGERSPOT_CLOUD_ID');
            $apiToken = env('FINGERSPOT_API_TOKEN');
            
            if (!$apiToken || !$cloudId) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Token atau Cloud ID belum dikonfigurasi',
                ]);
            }

            $url = "https://developer.fingerspot.io/api/get_all_pin";
            
            $postData = [
                'trans_id' => "1",
                'cloud_id' => $cloudId,
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiToken
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                return response()->json([
                    'success' => false,
                    'message' => 'CURL Error: ' . $error,
                ]);
            }
            
            $responseData = json_decode($response, true);
            
            Log::info('Get All PINs Response', [
                'http_code' => $httpCode,
                'response' => $responseData
            ]);
            
            if ($httpCode === 200 && isset($responseData['success']) && $responseData['success'] === true) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData,
                    'pins' => $responseData['data'] ?? [],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil daftar PIN',
                    'http_code' => $httpCode,
                    'response' => $responseData,
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Sync all employee names from Fingerspot.io
     * Mengambil nama karyawan dari API berdasarkan PIN yang sudah ada di database
     */
    public function syncEmployeeNames()
    {
        try {
            $cloudId = env('FINGERSPOT_CLOUD_ID');
            $apiToken = env('FINGERSPOT_API_TOKEN');
            
            if (!$apiToken || !$cloudId) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Token atau Cloud ID belum dikonfigurasi',
                ]);
            }

            // Ambil semua employee yang sudah ada di database
            $employees = Employee::whereNotNull('employee_id')->get();
            
            if ($employees->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada data karyawan di database',
                    'hint' => 'Data karyawan akan otomatis masuk saat ada aktivitas absensi via webhook, atau gunakan Sync Attendance terlebih dahulu',
                ]);
            }
            
            $updated = 0;
            $failed = 0;
            $results = [];
            
            foreach ($employees as $employee) {
                $pin = $employee->employee_id;
                
                // Get user info dari API
                $userInfo = $this->fetchUserInfoFromAPI($pin, $cloudId, $apiToken);
                
                if ($userInfo && isset($userInfo['data'])) {
                    $data = $userInfo['data'];
                    
                    // Extract nama dari berbagai kemungkinan field
                    $name = $data['name'] 
                         ?? $data['personname'] 
                         ?? $data['fullname'] 
                         ?? $data['person_name']
                         ?? null;
                    
                    if ($name && $name !== $employee->name) {
                        $oldName = $employee->name;
                        $employee->name = $name;
                        
                        // Update PIN jika ada
                        if (!$employee->pin && isset($data['pin'])) {
                            $employee->pin = $data['pin'];
                        }
                        
                        $employee->save();
                        $updated++;
                        
                        $results[] = [
                            'pin' => $pin,
                            'old_name' => $oldName,
                            'new_name' => $name,
                            'status' => 'updated',
                        ];
                        
                        Log::info('Updated employee name from API', [
                            'pin' => $pin,
                            'old_name' => $oldName,
                            'new_name' => $name,
                        ]);
                    } else {
                        $results[] = [
                            'pin' => $pin,
                            'name' => $employee->name,
                            'status' => 'no_change',
                        ];
                    }
                } else {
                    $failed++;
                    $results[] = [
                        'pin' => $pin,
                        'name' => $employee->name,
                        'status' => 'failed',
                    ];
                }
                
                // Sleep sebentar untuk menghindari rate limit
                usleep(200000); // 0.2 detik
            }
            
            return response()->json([
                'success' => true,
                'message' => "Selesai sync {$updated} nama karyawan dari {$employees->count()} total karyawan",
                'summary' => [
                    'total_employees' => $employees->count(),
                    'updated' => $updated,
                    'failed' => $failed,
                    'no_change' => $employees->count() - $updated - $failed,
                ],
                'details' => $results,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Sync employee names error', [
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
     * Helper function to fetch user info from Fingerspot API
     */
    private function fetchUserInfoFromAPI($pin, $cloudId, $apiToken)
    {
        try {
            $url = "https://developer.fingerspot.io/api/get_userinfo";
            
            $postData = [
                'trans_id' => "1",
                'cloud_id' => $cloudId,
                'pin' => $pin,
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiToken
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                return json_decode($response, true);
            }
            
            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to fetch user info', [
                'pin' => $pin,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
    
    /**
     * Clear all local data (employees and attendances)
     * Gunakan ini jika ingin reset semua data dan sync ulang dari mesin
     */
    public function clearLocalData()
    {
        try {
            // Hitung dulu sebelum dihapus
            $deletedAttendances = Attendance::count();
            $deletedEmployees = Employee::count();
            
            DB::beginTransaction();
            
            // Hapus semua data absensi terlebih dahulu (karena ada foreign key ke employees)
            Attendance::query()->delete();
            
            // Hapus semua data karyawan
            Employee::query()->delete();
            
            // Hapus webhook logs jika ada
            try {
                DB::table('fingerspot_webhook_logs')->delete();
            } catch (\Exception $e) {
                // Table mungkin tidak ada
                Log::warning('Failed to delete webhook logs', ['error' => $e->getMessage()]);
            }
            
            DB::commit();
            
            Log::info('Local data cleared successfully', [
                'deleted_employees' => $deletedEmployees,
                'deleted_attendances' => $deletedAttendances,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Semua data lokal berhasil dihapus',
                'deleted' => [
                    'employees' => $deletedEmployees,
                    'attendances' => $deletedAttendances,
                ],
                'next_step' => 'Silakan jalankan Sync Attendance untuk mengambil data baru dari mesin',
            ]);
            
        } catch (\Exception $e) {
            // Rollback jika ada error
            DB::rollBack();
            
            Log::error('Clear local data error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'hint' => 'Coba refresh halaman dan ulangi lagi',
            ]);
        }
    }
    
    /**
     * Sync attendance dengan filter waktu spesifik (hari ini saja)
     * Untuk menghindari data cache lama dari server Fingerspot
     */
    public function syncTodayOnly()
    {
        try {
            $cloudId = env('FINGERSPOT_CLOUD_ID');
            $apiToken = env('FINGERSPOT_API_TOKEN');
            
            if (!$apiToken || !$cloudId) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Token atau Cloud ID belum dikonfigurasi',
                ]);
            }

            $url = "https://developer.fingerspot.io/api/get_attlog";
            
            // Hanya ambil data HARI INI untuk menghindari cache lama
            $today = date('Y-m-d');
            
            $postData = [
                'trans_id' => uniqid(), // Unique trans_id untuk menghindari cache
                'cloud_id' => $cloudId,
                'start_date' => $today,
                'end_date' => $today,
            ];
            
            Log::info('Sync today only - Request', $postData);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiToken
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection error: ' . $error,
                ]);
            }
            
            $responseData = json_decode($response, true);
            
            Log::info('Sync today only - Response', [
                'http_code' => $httpCode,
                'response' => $responseData,
            ]);
            
            if ($httpCode !== 200) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Error (HTTP ' . $httpCode . ')',
                    'http_code' => $httpCode,
                    'response' => $responseData,
                ]);
            }
            
            if (isset($responseData['error_code'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Error: ' . ($responseData['message'] ?? $responseData['error_code']),
                ]);
            }
            
            // Process data
            $usersSynced = 0;
            $attendancesSynced = 0;
            $uniqueUsers = [];
            
            if (isset($responseData['data']) && is_array($responseData['data'])) {
                $machine = AttendanceMachine::where('serial_number', $cloudId)->first();
                
                if (!$machine) {
                    $machine = AttendanceMachine::create([
                        'name' => 'Fingerspot Device',
                        'serial_number' => $cloudId,
                        'ip_address' => 'FDEVICE.COM',
                        'port' => 9003,
                        'location' => 'Auto-registered',
                        'is_active' => true,
                    ]);
                }
                
                foreach ($responseData['data'] as $record) {
                    $pin = $record['pin'] ?? $record['personid'] ?? null;
                    $name = $record['personname'] ?? $record['name'] ?? null;
                    $scanDate = $record['scan_date'] ?? $record['datetime'] ?? null;
                    
                    if (!$pin || !$scanDate) continue;
                    
                    // Validasi: hanya proses data hari ini
                    $recordDate = Carbon::parse($scanDate)->format('Y-m-d');
                    if ($recordDate !== $today) {
                        Log::warning('Skipping old data', [
                            'pin' => $pin,
                            'date' => $recordDate,
                            'expected' => $today,
                        ]);
                        continue;
                    }
                    
                    // Track unique users
                    if (!isset($uniqueUsers[$pin])) {
                        $employee = Employee::updateOrCreate(
                            ['employee_id' => $pin],
                            [
                                'name' => $name ?? "Employee $pin",
                                'pin' => $pin,
                                'is_active' => true,
                            ]
                        );
                        
                        $uniqueUsers[$pin] = $employee;
                        $usersSynced++;
                        
                        Log::info('New employee synced', [
                            'pin' => $pin,
                            'name' => $employee->name,
                        ]);
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
                'message' => "Sync hari ini selesai: {$usersSynced} user baru, {$attendancesSynced} absensi",
                'date' => $today,
                'synced' => [
                    'users' => $usersSynced,
                    'attendances' => $attendancesSynced,
                ],
                'total_data_received' => isset($responseData['data']) ? count($responseData['data']) : 0,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Sync today only error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
}
