<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run role and permission seeder first
        $this->call(RolePermissionSeeder::class);

        // Create Super Admin user
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        // Assign super admin role
        $superAdmin->assignRole('super_admin');

        $this->command->info('Super Admin created:');
        $this->command->info('Email: superadmin@gmail.com');
        $this->command->info('Password: password123');
        $this->command->info('Role: super_admin');
        $this->command->info('Total Permissions: ' . $superAdmin->getAllPermissions()->count());
    }
}
