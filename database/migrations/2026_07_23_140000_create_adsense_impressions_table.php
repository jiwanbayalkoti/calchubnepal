<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adsense_impressions', function (Blueprint $table): void {
            $table->id();
            $table->string('position', 64);
            $table->string('ad_slot', 64)->nullable();
            $table->string('source', 32)->default('unit'); // unit | advertisement
            $table->foreignId('advertisement_id')->nullable()->constrained('advertisements')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('device', 32)->nullable();
            $table->string('browser', 64)->nullable();
            $table->string('country', 2)->nullable();
            $table->timestamp('viewed_at')->useCurrent();

            $table->index(['position', 'viewed_at']);
            $table->index('viewed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adsense_impressions');
    }
};
