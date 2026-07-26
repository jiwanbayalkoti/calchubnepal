<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breath_hold_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('claim_token', 64)->unique();
            $table->string('certificate_code', 32)->nullable()->unique();
            $table->unsignedInteger('duration_ms');
            $table->decimal('duration_seconds', 8, 2);
            $table->string('band', 20); // poor|medium|healthy
            $table->timestamp('certificate_issued_at')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['band', 'created_at']);
            $table->index('certificate_issued_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breath_hold_results');
    }
};
