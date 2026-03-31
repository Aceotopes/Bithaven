<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
        ALTER TABLE locker_unlock_jobs 
        MODIFY status ENUM(
            'PENDING',
            'PROCESSING',
            'SUCCEEDED',
            'FAILED',
            'CANCELLED'
        ) NOT NULL DEFAULT 'PENDING'
    ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
        ALTER TABLE locker_unlock_jobs 
        MODIFY status ENUM(
            'PENDING',
            'PROCESSING',
            'SUCCEEDED',
            'FAILED',
        ) NOT NULL DEFAULT 'PENDING'
    ");
    }
};
