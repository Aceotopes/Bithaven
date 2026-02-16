<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['username' => 'Superadmin'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('pass123'),
                'role' => 'SUPER_ADMIN',
                'status' => 'ACTIVE',
            ]
        );

        Admin::updateOrCreate(
            ['username' => 'Admin01'],
            [
                'name' => 'Admin One',
                'password' => Hash::make('pass123'),
                'role' => 'ADMIN',
                'status' => 'ACTIVE',
            ]
        );
    }
}
