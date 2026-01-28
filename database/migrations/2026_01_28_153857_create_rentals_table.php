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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('locker_id')
                ->constrained()
                ->restrictOnDelete();

            $table->dateTime('start_time');
            $table->dateTime('end_time');

            $table->enum('status', [
                'ACTIVE',
                'EXPIRED',
                'ENDED',
                'CANCELLED'
            ])->default('ACTIVE');

            $table->dateTime('ended_at')->nullable();
            $table->enum('ended_by', ['USER', 'SYSTEM', 'ADMIN'])->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
