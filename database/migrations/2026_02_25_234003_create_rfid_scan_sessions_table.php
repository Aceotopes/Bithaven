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
        Schema::create('rfid_scan_sessions', function (Blueprint $table) {
            $table->id();

            $table->string('kiosk_id');
            $table->foreignId('admin_id')->constrained('admins');

            $table->enum('status', [
                'PENDING',
                'COMPLETED',
                'CANCELLED',
                'EXPIRED'
            ])->default('PENDING');

            $table->string('rfid_uid')->nullable();

            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['kiosk_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfid_scan_sessions');
    }
};
