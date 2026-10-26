<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun DEMO saja (bukan kredensial asli). Cantumkan di README.
        $users = [
            ['name' => 'Admin Demo', 'email' => 'admin@demo.test', 'role' => 'admin'],
            ['name' => 'Owner Demo', 'email' => 'owner@demo.test', 'role' => 'admin'],
            ['name' => 'Staff Demo', 'email' => 'staff@demo.test', 'role' => 'staff'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name'     => $user['name'],
                    'password' => Hash::make('password'),
                    'role'     => $user['role'],
                ]
            );
        }
    }
}