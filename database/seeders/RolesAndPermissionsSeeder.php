<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'manage products']);
        Permission::create(['name' => 'manage clients']);
        Permission::create(['name' => 'view analytics']);
        Permission::create(['name' => 'manage invoices']);
        Permission::create(['name' => 'manage returns']);

        // Create roles and assign existing permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'manage products',
            'manage clients',
            'view analytics',
            'manage invoices',
            'manage returns',
        ]);

        // Optionally, create other roles (e.g., client) with limited permissions
        $clientRole = Role::create(['name' => 'client']);
        $clientRole->givePermissionTo([
            // Assign client-specific permissions if any
        ]);
    }
}
