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
            ['username' => 'Bithaven'],
            [
                'name' => 'Willen Mark Manzanas',
                'password' => Hash::make('pass123'),
                'role' => 'SUPER_ADMIN',
                'status' => 'ACTIVE',
            ]
        );

        Admin::updateOrCreate(
            ['username' => 'Bithaven_SuperAdmin'],
            [
                'name' => 'Bithaven_team',
                'password' => Hash::make('pass123'),
                'role' => 'SUPER_ADMIN',
                'status' => 'ACTIVE',
            ]
        );

        Admin::updateOrCreate(
            ['username' => 'Bithaven_Admin'],
            [
                'name' => 'Admin One',
                'password' => Hash::make('pass123'),
                'role' => 'ADMIN',
                'status' => 'ACTIVE',
            ]
        );
    }
}
