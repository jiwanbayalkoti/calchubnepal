<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('phone', 30)->nullable();
            $table->string('logo')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('user_id');
            $table->index('status');
            $table->index('company_name');
        });

        Schema::table('advertisements', function (Blueprint $table): void {
            if (! Schema::hasColumn('advertisements', 'advertiser_id')) {
                $table->foreignId('advertiser_id')->nullable()->after('id')->constrained('advertisers')->nullOnDelete();
            }
            if (! Schema::hasColumn('advertisements', 'banner_size')) {
                $table->string('banner_size', 32)->nullable()->after('position');
            }
            if (! Schema::hasColumn('advertisements', 'assigned_by')) {
                $table->foreignId('assigned_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('advertisements', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assigned_by');
            }
            if (! Schema::hasColumn('advertisements', 'status')) {
                $table->enum('status', ['draft', 'active', 'paused', 'expired'])->default('active')->after('is_active');
            }
        });

        if (Schema::hasColumn('advertisements', 'status')) {
            \Illuminate\Support\Facades\DB::table('advertisements')
                ->where('is_active', false)
                ->where('status', 'active')
                ->update(['status' => 'paused']);
        }

        Schema::create('advertisement_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->foreignId('advertiser_id')->constrained('advertisers')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['advertiser_id', 'advertisement_id']);
        });

        Schema::create('advertisement_impressions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->foreignId('advertiser_id')->nullable()->constrained('advertisers')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('device', 32)->nullable();
            $table->string('browser', 64)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamp('viewed_at')->useCurrent();

            $table->index(['advertiser_id', 'viewed_at']);
            $table->index(['advertisement_id', 'viewed_at']);
        });

        Schema::create('advertisement_clicks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->foreignId('advertiser_id')->nullable()->constrained('advertisers')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('device', 32)->nullable();
            $table->string('browser', 64)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamp('clicked_at')->useCurrent();

            $table->index(['advertiser_id', 'clicked_at']);
            $table->index(['advertisement_id', 'clicked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_clicks');
        Schema::dropIfExists('advertisement_impressions');
        Schema::dropIfExists('advertisement_assignments');

        Schema::table('advertisements', function (Blueprint $table): void {
            foreach (['advertiser_id', 'banner_size', 'assigned_by', 'assigned_at', 'status'] as $column) {
                if (Schema::hasColumn('advertisements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('advertisers');
    }
};
