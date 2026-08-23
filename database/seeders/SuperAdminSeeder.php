<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin@karteks-energy-solution.com';
        $password = 'KarteksAdmin2026!';

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin KARTEKS',
                'phone' => '+6281545326426',
                'phone_verified_at' => now(),
                'gender' => 'male',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'remember_token' => Str::random(10),
            ]
        );

        if (! $user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }

        $this->command->info('===========================================');
        $this->command->info('SUPER ADMIN USER CREATED');
        $this->command->info('Email:    '.$email);
        $this->command->info('Password: '.$password);
        $this->command->info('===========================================');
    }
}