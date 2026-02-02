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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('rental_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('penalty_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->decimal('amount', 10, 2);

            $table->enum('method', [
                'CASH',
                'ADMIN',
            ]);

            // Reference / receipt / transaction ID
            $table->string('reference_code')
                ->nullable()
                ->unique();

            $table->enum('status', [
                'COMPLETED',
                'FAILED',
            ])->default('COMPLETED');

            $table->timestamp('paid_at');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
