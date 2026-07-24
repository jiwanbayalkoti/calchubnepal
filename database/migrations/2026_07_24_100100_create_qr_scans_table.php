<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_code_id')->constrained('qr_codes')->cascadeOnDelete();
            $table->timestamp('scanned_at')->useCurrent()->index();
            $table->char('country', 2)->nullable()->index();
            $table->string('device', 20)->nullable()->index();
            $table->string('browser', 40)->nullable()->index();
            $table->string('os', 40)->nullable()->index();
            $table->string('referrer', 255)->nullable();
            $table->string('ip_hash', 64)->nullable()->index();
            $table->string('ip_truncated', 45)->nullable();
            $table->string('session_id', 100)->nullable()->index();
            $table->text('user_agent')->nullable();

            $table->index(['qr_code_id', 'scanned_at']);
            $table->index(['qr_code_id', 'country']);
            $table->index(['qr_code_id', 'device']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_scans');
    }
};
