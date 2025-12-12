<?php
/**
 * Script untuk memperbaiki nama karyawan yang masih "Employee X"
 * dengan memanggil API get_userinfo untuk setiap PIN
 * 
 * Jalankan: php fix_employee_names.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;

echo "=================================================\n";
echo "FIX EMPLOYEE NAMES FROM FINGERSPOT\n";
echo "=================================================\n\n";

$apiToken = env('FINGERSPOT_API_TOKEN');
$cloudId = env('FINGERSPOT_CLOUD_ID');

if (empty($apiToken) || empty($cloudId)) {
    die("❌ ERROR: API Token atau Cloud ID belum dikonfigurasi di .env\n");
}

// Cari semua karyawan dengan nama "Employee X"
$employees = Employee::where('name', 'LIKE', 'Employee %')->get();

echo "Ditemukan {$employees->count()} karyawan dengan nama default\n\n";

if ($employees->isEmpty()) {
    die("✅ Semua karyawan sudah punya nama yang benar!\n");
}

$fixed = 0;
$failed = 0;

foreach ($employees as $employee) {
    echo "Processing PIN: {$employee->pin} (Current: {$employee->name})... ";
    
    // Call API get_userinfo
    $url = "https://developer.fingerspot.io/api/get_userinfo";
    
    $postData = [
        'trans_id' => "1",
        'cloud_id' => $cloudId,
        'pin' => $employee->pin,
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $responseData = json_decode($response, true);
        
        // Cek berbagai kemungkinan format response
        $name = null;
        
        if (isset($responseData['data'])) {
            $userData = $responseData['data'];
            $name = $userData['personname'] 
                 ?? $userData['name']
                 ?? $userData['person_name']
                 ?? $userData['fullname']
                 ?? $userData['full_name']
                 ?? null;
        } elseif (isset($responseData['personname'])) {
            $name = $responseData['personname'];
        } elseif (isset($responseData['name'])) {
            $name = $responseData['name'];
        }
        
        if ($name) {
            $employee->name = $name;
            $employee->save();
            $fixed++;
            echo "✅ Updated to: {$name}\n";
        } else {
            $failed++;
            echo "❌ No name in API response\n";
            echo "   Response: " . json_encode($responseData) . "\n";
        }
    } else {
        $failed++;
        echo "❌ HTTP Error: {$httpCode}\n";
    }
    
    usleep(300000); // 0.3 second delay
}

echo "\n=================================================\n";
echo "SUMMARY:\n";
echo "Total processed: {$employees->count()}\n";
echo "✅ Fixed      : {$fixed}\n";
echo "❌ Failed     : {$failed}\n";
echo "=================================================\n";

if ($fixed > 0) {
    echo "\n💡 Silakan refresh halaman Data Karyawan untuk melihat perubahan\n";
}
