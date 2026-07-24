<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_workspaces', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 80)->unique();
            $table->string('logo_path')->nullable();
            $table->string('brand_primary', 7)->default('#0B6E4F');
            $table->string('brand_secondary', 7)->default('#F4A259');
            $table->string('custom_domain')->nullable()->unique();
            $table->boolean('white_label_enabled')->default(false);
            $table->string('support_email')->nullable();
            $table->text('redirect_footer')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('qr_workspace_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('qr_workspaces')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('role', 20)->default('member'); // owner|admin|member|viewer
            $table->string('invited_email')->nullable();
            $table->string('invite_token', 64)->nullable()->unique();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['workspace_id', 'user_id']);
            $table->index(['workspace_id', 'role']);
        });

        Schema::create('qr_brand_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('workspace_id')->nullable()->constrained('qr_workspaces')->nullOnDelete();
            $table->string('name');
            $table->json('style_json');
            $table->string('logo_path')->nullable();
            $table->boolean('is_default')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('qr_campaigns', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('workspace_id')->nullable()->constrained('qr_workspaces')->nullOnDelete();
            $table->string('name');
            $table->string('slug', 100);
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
            $table->index(['workspace_id', 'status']);
        });

        Schema::create('qr_bulk_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('workspace_id')->nullable()->constrained('qr_workspaces')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('qr_campaigns')->nullOnDelete();
            $table->foreignId('brand_template_id')->nullable()->constrained('qr_brand_templates')->nullOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->string('input_path')->nullable();
            $table->string('output_zip_path')->nullable();
            $table->json('error_log')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('provider', 40)->default('manual');
            $table->string('provider_reference')->nullable()->index();
            $table->string('status', 20)->default('pending')->index();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->json('meta')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::table('qr_codes', function (Blueprint $table) {
            if (! Schema::hasColumn('qr_codes', 'workspace_id')) {
                $table->foreignId('workspace_id')->nullable()->after('user_id')->constrained('qr_workspaces')->nullOnDelete();
            }
            if (! Schema::hasColumn('qr_codes', 'campaign_id')) {
                $table->foreignId('campaign_id')->nullable()->after('workspace_id')->constrained('qr_campaigns')->nullOnDelete();
            }
            if (! Schema::hasColumn('qr_codes', 'brand_template_id')) {
                $table->foreignId('brand_template_id')->nullable()->after('campaign_id')->constrained('qr_brand_templates')->nullOnDelete();
            }
        });

        Schema::table('qr_scans', function (Blueprint $table) {
            if (! Schema::hasColumn('qr_scans', 'city')) {
                $table->string('city', 80)->nullable()->after('country')->index();
            }
            if (! Schema::hasColumn('qr_scans', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('city');
            }
            if (! Schema::hasColumn('qr_scans', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('qr_scans', function (Blueprint $table) {
            foreach (['city', 'latitude', 'longitude'] as $col) {
                if (Schema::hasColumn('qr_scans', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('qr_codes', function (Blueprint $table) {
            foreach (['brand_template_id', 'campaign_id', 'workspace_id'] as $col) {
                if (Schema::hasColumn('qr_codes', $col)) {
                    $table->dropConstrainedForeignId($col);
                }
            }
        });

        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('qr_bulk_jobs');
        Schema::dropIfExists('qr_campaigns');
        Schema::dropIfExists('qr_brand_templates');
        Schema::dropIfExists('qr_workspace_members');
        Schema::dropIfExists('qr_workspaces');
    }
};
