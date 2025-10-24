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
            'email' => 'john@elitech.com',
            'role' => 'manager',
            'module_access' => 'ppic,production',
            'password' => Hash::make('password123'),
        ]);

        // Staff PPIC
        User::create([
            'name' => 'Ellie',
            'email' => 'ellie@elitech.com',
            'role' => 'staff',
            'module_access' => 'ppic',
            'password' => Hash::make('password123'),
        ]);

        // Staff Production
        User::create([
            'name' => 'Citra',
            'email' => 'citra@elitech.com',
            'role' => 'staff',
            'module_access' => 'production',
            'password' => Hash::make('password123'),
        ]);
    }
}