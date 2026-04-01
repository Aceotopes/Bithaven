<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\Hash;

class AdminSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminSetting::updateOrCreate(
            ['id' => 1],
            [
                'emergency_pin' => Hash::make('1234') // default PIN
            ]
        );
    }
}
