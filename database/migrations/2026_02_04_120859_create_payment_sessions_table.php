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
        Schema::create('payment_sessions', function (Blueprint $table) {
            $table->id();

            // Which kiosk owns this session (future-proofing)
            $table->string('kiosk_id');

            // What is being paid
            $table->enum('context_type', ['RENTAL', 'PENALTY']);

            // Targets (resolved only on completion)
            $table->foreignId('locker_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('penalty_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Rental-specific intent
            $table->unsignedInteger('duration_hours')->nullable();

            // Money
            $table->decimal('amount_due', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);

            // Lifecycle
            $table->enum('status', [
                'ACTIVE',
                'CANCELLED',
                'EXPIRED',
                'COMPLETED'
            ])->default('ACTIVE');

            // Safety / recovery
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // One active session per kiosk
            $table->index(['kiosk_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_sessions');
    }
};
