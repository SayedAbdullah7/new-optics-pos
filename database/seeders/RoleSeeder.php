<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Create default roles and assign permissions.
     * Run after PermissionUpdateSeeder.
     */
    public function run(): void
    {
        $allPermissions = Permission::pluck('name')->toArray();

        Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web'],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Full access to all modules and settings.',
            ]
        )->syncPermissions($allPermissions);
    }
}
