<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'admin',
            'last_name_primary' => 'admin',
            'last_name_secondary' => 'admin',
            'phone' => '7224730020',
            'role' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123') 
        ]);
    }
}
