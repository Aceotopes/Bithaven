<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use phpDocumentor\Reflection\Types\Nullable;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('locker_unlock_tokens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('locker_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('reason', [
                'RENTAL_START',
                'RENTAL_END',
                'PENALTY_SETTLED',
                'ADMIN_OVERRIDE',
            ]);

            $table->enum('authorized_by', [
                'SYSTEM',
                'ADMIN',
            ])->default('SYSTEM');

            $table->timestamp('issued_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->foreignId('rental_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('penalty_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('admin_id')->nullable();

            $table->timestamps();

            // Safety indexes
            $table->index(['locker_id', 'consumed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locker_unlock_tokens');
    }
};
