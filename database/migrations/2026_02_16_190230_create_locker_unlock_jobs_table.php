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
        Schema::create('locker_unlock_jobs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('unlock_token_id')
                ->constrained('locker_unlock_tokens')
                ->cascadeOnDelete();

            $table->foreignId('locker_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('max_attempts')->default(3);

            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('succeeded_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->enum('status', [
                'PENDING',
                'PROCESSING',
                'SUCCEEDED',
                'FAILED'
            ])->default('PENDING');

            $table->timestamps();

            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locker_unlock_jobs');
    }
};
