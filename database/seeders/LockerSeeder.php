<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LockerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lockers = [];

        for ($i = 1; $i <= 15; $i++) {
            $lockers[] = [
                'locker_number' => $i,
                'status' => 'AVAILABLE',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('lockers')->insert($lockers);
    }
}
