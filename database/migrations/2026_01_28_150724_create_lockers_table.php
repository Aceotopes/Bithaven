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
        Schema::create('lockers', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('locker_number')->unique();

            $table->string('location', 100)->nullable();

            $table->enum('status', ['AVAILABLE', 'OUT_OF_SERVICE'])
                ->default('AVAILABLE');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lockers');
    }
};
