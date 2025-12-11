<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing users to use Spatie roles
        $users = User::all();
        
        foreach ($users as $user) {
            if ($user->role) {
                // Check if role exists, if not create it
                $role = Role::firstOrCreate(['name' => $user->role]);
                
                // Assign role to user
                $user->assignRole($role);
            }
        }
        
        // After migrating, we can drop the role column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the role column
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable();
        });
        
        // Migrate roles back to the role column
        $users = User::all();
        
        foreach ($users as $user) {
            $userRoles = $user->getRoleNames();
            if ($userRoles->isNotEmpty()) {
                $user->role = $userRoles->first();
                $user->save();
            }
        }
    }
};
