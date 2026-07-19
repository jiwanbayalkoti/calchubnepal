<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('usage_events');

        if (Schema::hasColumn('subscription_plans', 'feature_flags')) {
            Schema::table('subscription_plans', function (Blueprint $table): void {
                $table->dropColumn('feature_flags');
            });
        }
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table): void {
            $table->json('feature_flags')->nullable()->after('features');
        });

        Schema::create('usage_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event', 64);
            $table->nullableMorphs('subject');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'event', 'created_at']);
        });
    }
};
