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
                'COIN',
                'ADMIN_OVERRIDE'
            ]);

            $table->enum('status', ['SUCCESS', 'FAILED']);

            $table->string('reference_code', 100)->nullable();

            $table->dateTime('paid_at');

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
