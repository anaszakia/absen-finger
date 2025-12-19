<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\FingerspotWebhookController;
use App\Http\Controllers\SalaryComponentController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\AllowanceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\DebugController;


// Public routes for Fingerspot.io webhook (no authentication required)
Route::prefix('api')->group(function () {
    // Fingerspot.io webhook endpoint
    Route::any('/fingerspot/webhook', [FingerspotWebhookController::class, 'receive'])
        ->name('fingerspot.webhook.receive');
    Route::get('/fingerspot/test', [FingerspotWebhookController::class, 'test'])
        ->name('fingerspot.webhook.test');
    Route::get('/fingerspot/check-connection', [FingerspotWebhookController::class, 'checkConnection'])
        ->name('fingerspot.check-connection');
    Route::post('/fingerspot/sync-users', [FingerspotWebhookController::class, 'syncUsers'])
        ->name('fingerspot.sync-users');
    Route::get('/fingerspot/employees', [FingerspotWebhookController::class, 'getEmployeesFromWebhook'])
        ->name('fingerspot.get-employees');
    Route::get('/fingerspot/all-pins', [FingerspotWebhookController::class, 'getAllPins'])
        ->name('fingerspot.get-all-pins');
    Route::get('/fingerspot/user-info/{pin}', [FingerspotWebhookController::class, 'getUserInfo'])
        ->name('fingerspot.get-user-info');
    Route::post('/fingerspot/sync-employee-names', [FingerspotWebhookController::class, 'syncEmployeeNames'])
        ->name('fingerspot.sync-employee-names');
    Route::delete('/fingerspot/clear-local-data', [FingerspotWebhookController::class, 'clearLocalData'])
        ->name('fingerspot.clear-local-data');
    Route::post('/fingerspot/sync-today', [FingerspotWebhookController::class, 'syncTodayOnly'])
        ->name('fingerspot.sync-today');
    Route::post('/fingerspot/sync-after-reset', [FingerspotWebhookController::class, 'syncAfterReset'])
        ->name('fingerspot.sync-after-reset');
    Route::post('/fingerspot/sync-last-days', [FingerspotWebhookController::class, 'syncLastDays'])
        ->name('fingerspot.sync-last-days');
    Route::delete('/fingerspot/clear-reset-filter', [FingerspotWebhookController::class, 'clearResetFilter'])
        ->name('fingerspot.clear-reset-filter');
});


// hanya bisa diakses tamu (belum login)
Route::middleware('guest')->group(function () {
    // Form login
    Route::get('/login', [LoginController::class, 'showLoginForm'])
         ->name('login');

    // Proses login
    Route::post('/login', [LoginController::class, 'login'])
         ->middleware('log.sensitive')
         ->name('login.submit');

    // Form register
    Route::get('/register', [LoginController::class, 'showRegisterForm'])
         ->name('register');

    // Proses register
    Route::post('/register', [LoginController::class, 'register'])
         ->middleware('log.sensitive')
         ->name('register.submit');
    
    // Forgot Password Routes with OTP
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])
         ->name('password.request');
    
    Route::post('/forgot-password/send-otp', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendOTP'])
         ->name('password.sendOTP');
    
    Route::post('/forgot-password/verify-otp', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'verifyOTP'])
         ->name('password.verifyOTP');
    
    // Reset Password Routes (after OTP verification)
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])
         ->name('password.reset');
    
    Route::post('/reset-password', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])
         ->name('password.update');
});

// Logout (method POST demi keamanan; pakai @csrf di form logout)
Route::post('/logout', [LoginController::class, 'logout'])
     ->middleware(['auth', 'log.sensitive'])
     ->name('logout');



// Authenticated routes - Permission based access control
Route::middleware(['auth', 'log.sensitive'])->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->middleware('permission:edit profile')
        ->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])
        ->middleware('permission:edit profile')
        ->name('profile.update');
    
    // Additional profile routes
    Route::get('/profile/security', function() {
        return view('profile.security');
    })->name('profile.security');
    
    Route::get('/profile/settings', function() {
        return view('profile.settings');
    })->name('profile.settings');
    
    Route::get('/help', function() {
        return view('help.center');
    })->name('help.center');
    
    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])
        ->name('dashboard');
    
    // User management routes
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:view users')
        ->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])
        ->middleware('permission:create users')
        ->name('users.create');
    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:create users')
        ->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])
        ->middleware('permission:view users')
        ->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('permission:edit users')
        ->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:edit users')
        ->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:delete users')
        ->name('users.destroy');
    
    // Audit Log routes
    Route::get('/audit', [AuditLogController::class, 'index'])
        ->middleware('permission:view audit logs')
        ->name('audit.index');
    Route::get('/audit/{auditLog}', [AuditLogController::class, 'show'])
        ->middleware('permission:view audit logs')
        ->name('audit.show');
    Route::post('/audit/export', [AuditLogController::class, 'export'])
        ->middleware('permission:export audit logs')
        ->name('audit.export');
    
    // Role management routes
    Route::resource('roles', App\Http\Controllers\RoleController::class)
        ->middleware('permission:view roles');
    Route::get('/roles/{role}', [App\Http\Controllers\RoleController::class, 'show'])
        ->middleware('permission:view roles')
        ->name('roles.show');
    Route::get('/roles/{role}/edit', [App\Http\Controllers\RoleController::class, 'edit'])
        ->middleware('permission:edit roles')
        ->name('roles.edit');
    Route::put('/roles/{role}', [App\Http\Controllers\RoleController::class, 'update'])
        ->middleware('permission:edit roles')
        ->name('roles.update');
    Route::delete('/roles/{role}', [App\Http\Controllers\RoleController::class, 'destroy'])
        ->middleware('permission:delete roles')
        ->name('roles.destroy');
    
    // Permission management routes
    Route::resource('permissions', App\Http\Controllers\PermissionController::class)
        ->middleware('permission:view permissions');
    Route::get('/permissions/{permission}', [App\Http\Controllers\PermissionController::class, 'show'])
        ->middleware('permission:view permissions')
        ->name('permissions.show');
    Route::get('/permissions/{permission}/edit', [App\Http\Controllers\PermissionController::class, 'edit'])
        ->middleware('permission:edit permissions')
        ->name('permissions.edit');
    Route::put('/permissions/{permission}', [App\Http\Controllers\PermissionController::class, 'update'])
        ->middleware('permission:edit permissions')
        ->name('permissions.update');
    Route::delete('/permissions/{permission}', [App\Http\Controllers\PermissionController::class, 'destroy'])
        ->middleware('permission:delete permissions')
        ->name('permissions.destroy');
    
    // Employee management routes
    Route::resource('employees', EmployeeController::class)
        ->middleware('permission:view employees');
    
    // Fingerspot Setup Page
    Route::get('/machines/fingerspot/setup', function() {
        return view('machines.fingerspot-setup');
    })->middleware('permission:view machines')->name('machines.fingerspot-setup');
    
    // Attendance routes
    Route::get('/attendances', [AttendanceController::class, 'index'])
        ->middleware('permission:view attendances')
        ->name('attendances.index');
    Route::get('/attendances/import', [AttendanceController::class, 'showImport'])
        ->middleware('permission:create attendances')
        ->name('attendances.import');
    Route::post('/attendances/import', [AttendanceController::class, 'processImport'])
        ->middleware('permission:create attendances')
        ->name('attendances.import.process');
    Route::post('/attendances/export', [AttendanceController::class, 'export'])
        ->middleware('permission:export attendances')
        ->name('attendances.export');
    
    // Payroll Management Routes
    // Master Data - Komponen Gaji
    Route::resource('salary-components', SalaryComponentController::class)
        ->middleware('permission:view payroll');
    
    // Master Data - Potongan
    Route::resource('deductions', DeductionController::class)
        ->middleware('permission:view payroll');
    
    // Master Data - Tunjangan/Bonus
    Route::resource('allowances', AllowanceController::class)
        ->middleware('permission:view payroll');
    
    // Payroll/Penggajian
    Route::resource('payrolls', PayrollController::class)
        ->middleware('permission:view payroll');
    
    // Payroll History - Riwayat per periode
    Route::get('/payroll-history', [PayrollController::class, 'history'])
        ->middleware('permission:view payroll')
        ->name('payrolls.history');
    
    // Print slip gaji
    Route::get('/payrolls/{payroll}/slip', [PayrollController::class, 'slip'])
        ->middleware('permission:view payroll')
        ->name('payrolls.slip');
    
    // Generate payroll untuk periode tertentu
    Route::post('/payrolls/generate', [PayrollController::class, 'generate'])
        ->middleware('permission:create payroll')
        ->name('payrolls.generate');
    
    // Approve payroll
    Route::post('/payrolls/{payroll}/approve', [PayrollController::class, 'approve'])
        ->middleware('permission:approve payroll')
        ->name('payrolls.approve');
    
    // Recalculate payroll
    Route::post('/payrolls/{payroll}/recalculate', [PayrollController::class, 'recalculate'])
        ->middleware('permission:view payroll')
        ->name('payrolls.recalculate');
    
    // Pay payroll (mark as paid)
    Route::post('/payrolls/{payroll}/pay', [PayrollController::class, 'markAsPaid'])
        ->middleware('permission:pay payroll')
        ->name('payrolls.pay');
    
    // Debug attendance data
    Route::get('/debug/attendance', [DebugController::class, 'checkAttendance'])
        ->name('debug.attendance');
});

Route::redirect('/', '/login');
