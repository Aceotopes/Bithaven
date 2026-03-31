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
        Schema::table('rentals', function (Blueprint $table) {

            // Add duration_hours
            $table->integer('duration_hours')->nullable()->after('locker_id');

            //  Make times nullable
            $table->dateTime('start_time')->nullable()->change();
            $table->dateTime('end_time')->nullable()->change();

            //  Modify enum to include PENDING
            $table->enum('status', [
                'PENDING',
                'ACTIVE',
                'EXPIRED',
                'ENDED',
                'CANCELLED'
            ])->default('PENDING')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {

            $table->dropColumn('duration_hours');

            $table->dateTime('start_time')->nullable(false)->change();
            $table->dateTime('end_time')->nullable(false)->change();

            $table->enum('status', [
                'ACTIVE',
                'EXPIRED',
                'ENDED',
                'CANCELLED'
            ])->default('ACTIVE')->change();
        });
    }
};
