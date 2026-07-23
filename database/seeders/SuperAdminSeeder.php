<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('username', 'superadmin')->exists()) {
            $this->command->info('Super admin user already exists — skipped.');

            return;
        }

        $user = User::create([
            'username'             => 'superadmin',
            'name'                 => 'Super Administrator',
            'email'                => null,
            'password'             => Hash::make('Admin@12345'),
            'role'                 => 'super_admin',
            'is_active'            => true,
            'must_change_password' => true,
        ]);

        $user->assignRole('super_admin');

        $this->command->info('Super admin created: superadmin / Admin@12345 (change password on first login)');
    }
}
