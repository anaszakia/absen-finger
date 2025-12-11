<?php

// Test manual get_attlog sesuai dokumentasi

$cloudId = 'C263045107E1C26';
$apiToken = '2VBG6F40KLHJUHSH';
$url = "https://developer.fingerspot.io/api/get_attlog";

echo "=== TEST GET_ATTLOG API ===\n\n";

$tests = [
    [
        'trans_id' => '1',
        'cloud_id' => $cloudId,
        'start_date' => '2025-12-10',
        'end_date' => '2025-12-11',
    ],
    [
        'trans_id' => 1,
        'cloud_id' => $cloudId,
        'start_date' => '2025-12-10',
        'end_date' => '2025-12-11',
    ],
    [
        'trans_id' => time(),
        'cloud_id' => $cloudId,
        'start_date' => '2025-12-10',
        'end_date' => '2025-12-11',
    ],
];

foreach ($tests as $index => $postData) {
    echo "Test #" . ($index + 1) . "\n";
    echo str_repeat('-', 60) . "\n";
    echo "Data sent:\n";
    print_r($postData);
    
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
    
    echo "\nJSON sent: " . json_encode($postData) . "\n\n";
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    echo "Response:\n";
    $data = json_decode($response, true);
    print_r($data);
    
    echo "\n" . str_repeat('=', 60) . "\n\n";
    
    // Jika berhasil, stop
    if (isset($data['success']) && $data['success']) {
        echo "✓ BERHASIL!\n";
        break;
    }
}
