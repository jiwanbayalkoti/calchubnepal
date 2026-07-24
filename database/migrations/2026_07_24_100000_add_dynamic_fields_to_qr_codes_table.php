<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            if (! Schema::hasColumn('qr_codes', 'is_dynamic')) {
                $table->boolean('is_dynamic')->default(false)->after('is_saved')->index();
            }
            if (! Schema::hasColumn('qr_codes', 'short_code')) {
                $table->string('short_code', 16)->nullable()->unique()->after('uuid');
            }
            if (! Schema::hasColumn('qr_codes', 'destination_url')) {
                $table->text('destination_url')->nullable()->after('payload');
            }
            if (! Schema::hasColumn('qr_codes', 'status')) {
                $table->string('status', 20)->default('active')->after('is_dynamic')->index();
            }
            if (! Schema::hasColumn('qr_codes', 'password_hash')) {
                $table->string('password_hash')->nullable()->after('status');
            }
            if (! Schema::hasColumn('qr_codes', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('password_hash')->index();
            }
            if (! Schema::hasColumn('qr_codes', 'scan_count')) {
                $table->unsignedBigInteger('scan_count')->default(0)->after('expires_at');
            }
            if (! Schema::hasColumn('qr_codes', 'last_scanned_at')) {
                $table->timestamp('last_scanned_at')->nullable()->after('scan_count');
            }
        });

        Schema::table('qr_codes', function (Blueprint $table) {
            $table->index(['user_id', 'is_dynamic', 'created_at'], 'qr_codes_user_dynamic_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            try {
                $table->dropIndex('qr_codes_user_dynamic_created_index');
            } catch (\Throwable) {
                // index may not exist
            }

            $columns = [
                'is_dynamic',
                'short_code',
                'destination_url',
                'status',
                'password_hash',
                'expires_at',
                'scan_count',
                'last_scanned_at',
            ];

            $drop = array_values(array_filter($columns, fn (string $col) => Schema::hasColumn('qr_codes', $col)));
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
