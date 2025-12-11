<?php

/**
 * Test script untuk simulate webhook dari Fingerspot.io
 */

$webhookUrl = 'http://192.168.0.118:8000/api/fingerspot/webhook';

echo "=== TEST FINGERSPOT WEBHOOK ===\n";
echo "Target: $webhookUrl\n\n";

// Test 1: Attendance Data
echo "Test 1: Attendance Data\n";
echo "------------------------\n";

$attendanceData = [
    'type' => 'attendance',
    'cloud_id' => 'C263045107E1C26',
    'data' => [
        'pin' => '001',
        'personname' => 'Budi Santoso',
        'scan_date' => date('Y-m-d H:i:s'),
        'verify_mode' => 'FP',
    ]
];

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($attendanceData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Data sent:\n";
print_r($attendanceData);
echo "\nHTTP Code: $httpCode\n";
echo "Response: $response\n\n";

sleep(1);

// Test 2: Multiple Attendance Records
echo "Test 2: Multiple Attendance (Batch)\n";
echo "-------------------------------------\n";

$batchData = [
    'type' => 'attendance',
    'cloud_id' => 'C263045107E1C26',
    'data' => [
        [
            'pin' => '002',
            'personname' => 'Siti Aminah',
            'scan_date' => date('Y-m-d 08:15:00'),
        ],
        [
            'pin' => '003',
            'personname' => 'Ahmad Wijaya',
            'scan_date' => date('Y-m-d 08:30:00'),
        ],
        [
            'pin' => '004',
            'personname' => 'Dewi Lestari',
            'scan_date' => date('Y-m-d 07:45:00'),
        ],
    ]
];

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($batchData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Data sent: " . count($batchData['data']) . " records\n";
echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

sleep(1);

// Test 3: User Data
echo "Test 3: User/Person Data\n";
echo "-------------------------\n";

$userData = [
    'type' => 'user',
    'cloud_id' => 'C263045107E1C26',
    'data' => [
        'pin' => '005',
        'personname' => 'Rudi Hermawan',
        'privilege' => '0',
    ]
];

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Data sent:\n";
print_r($userData);
echo "\nHTTP Code: $httpCode\n";
echo "Response: $response\n\n";

sleep(1);

// Test 4: Check-out (same day, different time)
echo "Test 4: Check-out (Update)\n";
echo "---------------------------\n";

$checkoutData = [
    'type' => 'attendance',
    'cloud_id' => 'C263045107E1C26',
    'data' => [
        'pin' => '001',
        'personname' => 'Budi Santoso',
        'scan_date' => date('Y-m-d 17:30:00'), // Check-out time
        'verify_mode' => 'FP',
    ]
];

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($checkoutData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Data sent:\n";
print_r($checkoutData);
echo "\nHTTP Code: $httpCode\n";
echo "Response: $response\n\n";

echo "=== TEST SELESAI ===\n\n";
echo "Silakan cek:\n";
echo "1. Database: tabel attendances & employees\n";
echo "2. Log: storage/logs/laravel.log\n";
echo "3. Webhook Logs: tabel fingerspot_webhook_logs\n";
echo "4. Web: http://192.168.0.118:8000/attendances\n";
