<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ExpenseCategorySeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@lifefinanceos.com'],
            [
                'name'     => 'System Administrator',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        $demo = User::firstOrCreate(
            ['email' => 'demo@lifefinanceos.com'],
            [
                'name'     => 'Demo User',
                'password' => Hash::make('password'),
            ]
        );
        $demo->assignRole('user');
    }
}

