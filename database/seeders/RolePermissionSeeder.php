<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view personal finance',
            'manage personal finance',
            'view business finance',
            'manage business finance',
            'view family',
            'manage family',
            'view investments',
            'manage investments',
            'view assets',
            'manage assets',
            'view reports',
            'manage users',
            'manage roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);

        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->syncPermissions([
            'view personal finance',
            'manage personal finance',
            'view business finance',
            'manage business finance',
            'view family',
            'manage family',
            'view investments',
            'manage investments',
            'view assets',
            'manage assets',
            'view reports',
        ]);
    }
}
