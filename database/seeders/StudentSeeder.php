<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('students')->insert([
            [
                'student_number' => '22-150570',
                'first_name' => 'Ace Argee',
                'middle_name' => 'Felipe',
                'last_name' => 'Vizcarra',
                'year_level' => 'IV',
                'department' => 'Computer Engineering',
                'rfid_uid' => '0851967331',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_number' => '22-150692',
                'first_name' => 'Janssen',
                'middle_name' => 'Nano',
                'last_name' => 'Natino',
                'year_level' => 'IV',
                'department' => 'Computer Engineering',
                'rfid_uid' => '0852111539',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_number' => '22-150562',
                'first_name' => 'Jan Marini',
                'middle_name' => 'Piedad',
                'last_name' => 'Pastor',
                'year_level' => 'IV',
                'department' => 'Computer Engineering',
                'rfid_uid' => '0852368307',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_number' => '22-150585',
                'first_name' => 'Rhoda Jane',
                'middle_name' => 'Manuel',
                'last_name' => 'Lagran',
                'year_level' => 'IV',
                'department' => 'Computer Engineering',
                'rfid_uid' => '0853149619',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_number' => '22-150565',
                'first_name' => 'Shenine Grace',
                'middle_name' => 'Dahilig',
                'last_name' => 'Bautista',
                'year_level' => 'IV',
                'department' => 'Computer Engineering',
                'rfid_uid' => '0862145473',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_number' => '22-150567',
                'first_name' => 'Mark Alvin',
                'middle_name' => 'Valdez',
                'last_name' => 'Ramelb',
                'year_level' => 'IV',
                'department' => 'Computer Engineering',
                'rfid_uid' => '0852297155',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
