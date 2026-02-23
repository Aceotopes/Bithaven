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
        Schema::create('kiosk_events', function (Blueprint $table) {
            $table->id();

            $table->string('event_type', 50); // e.g. PENALTY_FROZEN, LOCKER_UNLOCKED
            $table->enum('level', ['INFO', 'WARNING', 'ERROR'])
                ->default('INFO');

            $table->text('message')->nullable();

            $table->json('payload')->nullable();

            $table->string('kiosk_id', 50)->nullable();

            $table->foreignId('student_id')->nullable()->constrained();
            $table->foreignId('admin_card_id')->nullable()->constrained();
            $table->foreignId('rental_id')->nullable()->constrained();
            $table->foreignId('penalty_id')->nullable()->constrained();
            $table->foreignId('payment_id')->nullable()->constrained();
            $table->foreignId('locker_id')->nullable()->constrained();
            $table->foreignId('unlock_token_id')->nullable()
                ->constrained('locker_unlock_tokens');

            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_type', 'created_at']);
            $table->index(['kiosk_id', 'created_at']);
            $table->index(['student_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kiosk_events');
    }
};
