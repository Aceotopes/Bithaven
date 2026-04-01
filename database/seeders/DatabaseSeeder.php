<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(StudentSeeder::class);
        $this->call(LockerSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(AdminCardSeeder::class);
        $this->call(AdminSettingSeeder::class);
    }
}
