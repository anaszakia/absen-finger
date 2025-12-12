<?php
/**
 * Test Fingerspot.io get_all_pin API dengan debugging lengkap
 * Jalankan: php test_fingerspot_get_pins_debug.php
 */

// Load Laravel .env
require __DIR__.'/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiToken = $_ENV['FINGERSPOT_API_TOKEN'] ?? '';
$cloudId = $_ENV['FINGERSPOT_CLOUD_ID'] ?? '';

echo "=================================================\n";
echo "FINGERSPOT GET ALL PIN - DEBUG TEST\n";
echo "=================================================\n\n";

echo "1. Konfigurasi:\n";
echo "   API Token: " . substr($apiToken, 0, 10) . "..." . substr($apiToken, -4) . "\n";
echo "   Cloud ID : $cloudId\n\n";

if (empty($apiToken) || empty($cloudId)) {
    die("❌ ERROR: API Token atau Cloud ID belum dikonfigurasi di file .env\n");
}

echo "2. Memanggil API get_all_pin...\n\n";

$url = "https://developer.fingerspot.io/api/get_all_pin";

$postData = [
    'trans_id' => "1",
    'cloud_id' => $cloudId,
];

echo "   URL       : $url\n";
echo "   POST Data : " . json_encode($postData, JSON_PRETTY_PRINT) . "\n\n";

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
curl_setopt($ch, CURLOPT_VERBOSE, true);

$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

rewind($verbose);
$verboseLog = stream_get_contents($verbose);

curl_close($ch);

echo "3. Response dari API:\n";
echo "   HTTP Code : $httpCode\n";

if ($error) {
    echo "   CURL Error: $error\n\n";
    die("❌ Gagal koneksi ke API\n");
}

echo "   Raw Response:\n";
echo "   " . str_repeat("-", 60) . "\n";
echo "   " . $response . "\n";
echo "   " . str_repeat("-", 60) . "\n\n";

$responseData = json_decode($response, true);

if (!$responseData) {
    die("❌ Response bukan JSON yang valid\n");
}

echo "4. Parsed Response:\n";
echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";

echo "5. Analisis Response:\n";
echo "   Keys yang tersedia: " . implode(', ', array_keys($responseData)) . "\n";

// Cek berbagai kemungkinan lokasi data PIN
$pins = [];
$dataLocation = 'not found';

if (isset($responseData['data']) && is_array($responseData['data'])) {
    $pins = $responseData['data'];
    $dataLocation = "responseData['data']";
} elseif (isset($responseData['pins']) && is_array($responseData['pins'])) {
    $pins = $responseData['pins'];
    $dataLocation = "responseData['pins']";
} elseif (isset($responseData['pin_list']) && is_array($responseData['pin_list'])) {
    $pins = $responseData['pin_list'];
    $dataLocation = "responseData['pin_list']";
} elseif (isset($responseData['pin']) && is_array($responseData['pin'])) {
    $pins = $responseData['pin'];
    $dataLocation = "responseData['pin']";
}

echo "   Lokasi data PIN: $dataLocation\n";
echo "   Jumlah PIN    : " . count($pins) . "\n\n";

if (!empty($pins)) {
    echo "✅ BERHASIL! Ditemukan " . count($pins) . " PIN:\n";
    foreach ($pins as $index => $pin) {
        $pinValue = is_array($pin) ? json_encode($pin) : $pin;
        echo "   [" . ($index + 1) . "] $pinValue\n";
        if ($index >= 9) {
            echo "   ... dan " . (count($pins) - 10) . " PIN lainnya\n";
            break;
        }
    }
    echo "\n";
    echo "💡 Tips: Sekarang Anda bisa sync karyawan dari menu Data Karyawan\n";
} else {
    echo "❌ TIDAK ADA PIN DITEMUKAN\n\n";
    echo "Kemungkinan penyebab:\n";
    echo "1. Cloud ID salah - Cek di developer.fingerspot.io → Devices\n";
    echo "2. Belum ada karyawan terdaftar di mesin fingerspot\n";
    echo "3. Mesin belum online/terhubung ke cloud\n\n";
    
    echo "Cara mengatasi:\n";
    echo "1. Login ke https://developer.fingerspot.io\n";
    echo "2. Buka menu 'Devices' di sidebar\n";
    echo "3. Cek Cloud ID mesin Anda (bukan Account ID)\n";
    echo "4. Update FINGERSPOT_CLOUD_ID di file .env\n";
    echo "5. Pastikan mesin sudah online (hijau)\n";
    echo "6. Pastikan ada karyawan terdaftar di mesin\n\n";
    
    echo "Alternatif (jika API tidak didukung):\n";
    echo "- Minta karyawan scan jari → Data otomatis masuk via webhook\n";
    echo "- Atau tambah karyawan manual di menu Data Karyawan\n";
}

echo "\n";
echo "VERBOSE LOG (untuk debugging):\n";
echo str_repeat("=", 60) . "\n";
echo $verboseLog;
echo str_repeat("=", 60) . "\n";
