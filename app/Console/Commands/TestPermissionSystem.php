<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestPermissionSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Spatie Permission System';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Spatie Permission System...');
        $this->newLine();
        
        // Test Super Admin User
        $superAdmin = \App\Models\User::where('email', 'superadmin@gmail.com')->first();
        if ($superAdmin) {
            $this->info('Super Admin User found: ' . $superAdmin->name);
            $this->info('Super Admin Roles: ' . implode(', ', $superAdmin->getRoleNames()->toArray()));
            $this->info('Super Admin Permissions: ' . implode(', ', $superAdmin->getAllPermissions()->pluck('name')->toArray()));
        } else {
            $this->error('Super Admin user not found!');
        }
        
        $this->newLine();
        
        // Test Regular User
        $user = \App\Models\User::where('email', 'user@gmail.com')->first();
        if ($user) {
            $this->info('Test User found: ' . $user->name);
            $this->info('User Roles: ' . implode(', ', $user->getRoleNames()->toArray()));
            $this->info('User Permissions: ' . implode(', ', $user->getAllPermissions()->pluck('name')->toArray()));
        } else {
            $this->error('Test user not found!');
        }
        
        $this->newLine();
        
        // Test All Roles
        $roles = \Spatie\Permission\Models\Role::all();
        $this->info('All Roles: ' . implode(', ', $roles->pluck('name')->toArray()));
        
        // Test All Permissions
        $permissions = \Spatie\Permission\Models\Permission::all();
        $this->info('All Permissions: ' . implode(', ', $permissions->pluck('name')->toArray()));
        
        $this->newLine();
        $this->info('Permission system test completed!');
    }
}
