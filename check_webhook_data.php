<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

echo "=== WEBHOOK DATA CHECK ===\n\n";

echo "Total Employees: " . Employee::count() . "\n";
echo "Total Attendances: " . Attendance::count() . "\n";
echo "Total Webhook Logs: " . DB::table('fingerspot_webhook_logs')->count() . "\n\n";

echo "--- Latest Employees (Created from Webhook) ---\n";
$employees = Employee::orderBy('created_at', 'desc')->take(5)->get();
foreach ($employees as $emp) {
    echo "ID: {$emp->employee_id} | Name: {$emp->name} | Created: {$emp->created_at}\n";
}

echo "\n--- Latest Attendances ---\n";
$attendances = Attendance::with('employee')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();
foreach ($attendances as $att) {
    echo "Date: {$att->date} | Employee: {$att->employee->name} | Check-in: {$att->check_in} | Check-out: " . ($att->check_out ?? 'null') . "\n";
}

echo "\n--- Webhook Logs ---\n";
$logs = DB::table('fingerspot_webhook_logs')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();
foreach ($logs as $log) {
    echo "Type: {$log->type} | Cloud ID: {$log->cloud_id} | Created: {$log->created_at}\n";
}

echo "\n=== CHECK COMPLETE ===\n";
