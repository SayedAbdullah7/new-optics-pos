<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            // Administration
            'users' => ['create', 'read', 'update', 'delete'],
            'roles' => ['create', 'read', 'update', 'delete'],
            // Sales & clients
            'clients' => ['create', 'read', 'update', 'delete'],
            'invoices' => ['create', 'read', 'update', 'delete'],
            'transactions' => ['create', 'read', 'update', 'delete'],
            // Purchases & vendors
            'vendors' => ['create', 'read', 'update', 'delete'],
            'bills' => ['create', 'read', 'update', 'delete'],
            // Catalog
            'categories' => ['create', 'read', 'update', 'delete'],
            'products' => ['create', 'read', 'update', 'delete'],
            'stock' => ['read'],
            // Lenses
            'lenses' => ['create', 'read', 'update', 'delete'],
            'lens-types' => ['create', 'read', 'update', 'delete'],
            'lens-brands' => ['create', 'read', 'update', 'delete'],
            // Other
            'expenses' => ['create', 'read', 'update', 'delete'],
            'range-powers' => ['read', 'update'],
            'multi-select-table' => ['read', 'update'],
            'system' => ['update'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $name = $action . '-' . $module;
                \App\Models\Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    [
                        'display_name' => ucfirst($action) . ' ' . ucfirst(str_replace('-', ' ', $module)),
                        'description' => 'Allow ' . $action . ' operations on ' . $module,
                    ]
                );
            }
        }
    }
}
