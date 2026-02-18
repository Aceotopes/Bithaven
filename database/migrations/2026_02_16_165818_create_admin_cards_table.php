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
        Schema::create('admin_cards', function (Blueprint $table) {
            $table->id();

            $table->string('card_label'); // Admin Card 01
            $table->string('rfid_uid', 50)->unique();

            $table->enum('status', ['ACTIVE', 'DISABLED'])
                ->default('ACTIVE');

            // TRACKING
            $table->string('assigned_to')->nullable();
            $table->timestamp('assigned_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_cards');
    }
};
