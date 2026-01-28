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
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rental_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->dateTime('started_at');

            $table->dateTime('settled_at')->nullable();

            $table->decimal('amount', 10, 2);

            $table->enum('status', ['ACTIVE', 'PAID'])
                ->default('ACTIVE');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
