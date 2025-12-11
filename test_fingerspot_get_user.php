<?php

// Test Fingerspot API get_userinfo

$cloudId = 'C263045107E1C26';
$apiToken = '2VBG6F40KLHJUHSH';
$url = "https://developer.fingerspot.io/api/get_userinfo";

echo "=== TEST FINGERSPOT API get_userinfo ===\n\n";

// Test dengan beberapa PIN
$testPins = ['1', '2', '3', '001', '002', '003'];

foreach ($testPins as $pin) {
    echo "Testing PIN: $pin\n";
    echo str_repeat('-', 50) . "\n";
    
    $postData = [
        'trans_id' => $cloudId . '_test_' . $pin,
        'cloud_id' => $cloudId,
        'pin' => $pin,
    ];
    
    echo "Request Data:\n";
    print_r($postData);
    echo "\n";
    
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
    
    echo "HTTP Code: $httpCode\n";
    if ($error) {
        echo "CURL Error: $error\n";
    }
    
    echo "Response:\n";
    $data = json_decode($response, true);
    if ($data) {
        print_r($data);
    } else {
        echo $response . "\n";
    }
    
    echo "\n" . str_repeat('=', 50) . "\n\n";
    
    // Jika berhasil dapat data, stop
    if (isset($data['pin']) && isset($data['personname'])) {
        echo "✓ User ditemukan! PIN: {$data['pin']}, Nama: {$data['personname']}\n";
        break;
    }
}

echo "\n=== TEST SELESAI ===\n";
