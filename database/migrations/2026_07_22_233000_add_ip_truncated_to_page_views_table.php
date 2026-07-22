<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            if (! Schema::hasColumn('page_views', 'ip_truncated')) {
                $table->string('ip_truncated', 45)->nullable()->after('ip_hash');
            }

            $table->index('country');
            $table->index('ip_truncated');
        });
    }

    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->dropIndex(['country']);
            $table->dropIndex(['ip_truncated']);

            if (Schema::hasColumn('page_views', 'ip_truncated')) {
                $table->dropColumn('ip_truncated');
            }
        });
    }
};
