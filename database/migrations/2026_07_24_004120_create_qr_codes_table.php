<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 100)->nullable()->index();
            $table->string('type', 32);
            $table->text('payload');
            $table->json('input_json')->nullable();
            $table->json('style_json')->nullable();
            $table->string('title')->nullable();
            $table->boolean('is_saved')->default(false)->index();
            $table->string('preview_path')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_saved', 'created_at']);
            $table->index(['session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
