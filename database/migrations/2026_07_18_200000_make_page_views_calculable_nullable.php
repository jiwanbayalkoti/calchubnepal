<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * page_views.calculable was required morphs(), which blocked recording
 * generic page hits (home, about, etc.). Make the morph nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $table): void {
            $table->dropMorphs('calculable');
        });

        Schema::table('page_views', function (Blueprint $table): void {
            $table->nullableMorphs('calculable');
        });
    }

    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table): void {
            $table->dropMorphs('calculable');
        });

        Schema::table('page_views', function (Blueprint $table): void {
            $table->morphs('calculable');
        });
    }
};
