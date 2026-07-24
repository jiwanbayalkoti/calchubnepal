<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (! Schema::hasColumn('blog_posts', 'related_qr_type')) {
                $table->string('related_qr_type', 40)->nullable()->after('meta_keywords');
                $table->index('related_qr_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (Schema::hasColumn('blog_posts', 'related_qr_type')) {
                $table->dropIndex(['related_qr_type']);
                $table->dropColumn('related_qr_type');
            }
        });
    }
};
