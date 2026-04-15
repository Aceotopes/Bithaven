<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE locker_unlock_tokens 
            MODIFY reason ENUM(
                'RENTAL_START',
                'RENTAL_END',
                'RENTAL_ACCESS',
                'PENALTY_SETTLED',
                'ADMIN_OVERRIDE'
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
        ALTER TABLE locker_unlock_tokens 
        MODIFY reason ENUM(
            'RENTAL_START',
            'RENTAL_END',
            'PENALTY_SETTLED',
            'ADMIN_OVERRIDE'
        )
    ");
    }
};
