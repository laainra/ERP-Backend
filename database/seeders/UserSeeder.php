<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Manager PPIC
        User::create([
            'name' => 'John',
            'email' => 'john.manager@elitech.com',
            'role' => 'manager',
            'password' => Hash::make('password123'),
        ]);

        // Staff PPIC
        User::create([
            'name' => 'Ellie',
            'email' => 'ellie.ppic@elitech.com',
            'role' => 'staff',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Mark',
            'email' => 'mark.ppic@elitech.com',
            'role' => 'staff',
            'password' => Hash::make('password123'),
        ]);

        // Staff Production
        User::create([
            'name' => 'Citra',
            'email' => 'citra.production@elitech.com',
            'role' => 'staff',
            'password' => Hash::make('password123'),
        ]);
        User::create([
            'name' => 'Dani',
            'email' => 'dani.production@elitech.com',
            'role' => 'staff',
            'password' => Hash::make('password123'),
        ]);

    }
}