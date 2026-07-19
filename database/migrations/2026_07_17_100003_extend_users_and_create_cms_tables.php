<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->extendUsersTable();

        $this->createSubscriptionTables();
        $this->createApiKeysTable();
        $this->createBlogTables();
        $this->createAdvertisementsTable();
        $this->createSeoPagesTable();
        $this->createContactMessagesTable();
        $this->createFeedbackTable();
        $this->createSettingsTable();
        $this->createAiTables();
        $this->createActivityLogsTable();
        $this->createPageViewsTable();
        $this->createTranslationsTable();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
        Schema::dropIfExists('page_views');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('ai_logs');
        Schema::dropIfExists('ai_prompts');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('seo_pages');
        Schema::dropIfExists('advertisements');
        Schema::dropIfExists('blog_post_calculator');
        Schema::dropIfExists('blog_post_tag');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');

        $this->revertUsersTable();
    }

    /**
     * Extend the users table with profile, RBAC, subscription, and audit columns.
     */
    private function extendUsersTable(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('role_id')->nullable()->after('id')
                ->constrained('roles')->nullOnDelete();

            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('locale', 5)->default('en')->after('avatar');
            $table->boolean('is_active')->default(true)->after('locale');
            $table->boolean('is_premium')->default(false)->after('is_active');
            $table->timestamp('premium_expires_at')->nullable()->after('is_premium');
            $table->timestamp('last_login_at')->nullable()->after('premium_expires_at');

            $table->foreignId('created_by')->nullable()->after('last_login_at')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->after('updated_by')
                ->constrained('users')->nullOnDelete();

            $table->softDeletes();

            $table->index('is_active');
            $table->index('is_premium');
        });
    }

    private function revertUsersTable(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);

            $table->dropColumn([
                'role_id',
                'phone',
                'avatar',
                'locale',
                'is_active',
                'is_premium',
                'premium_expires_at',
                'last_login_at',
                'created_by',
                'updated_by',
                'deleted_by',
                'deleted_at',
            ]);
        });
    }

    private function createSubscriptionTables(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->enum('billing_period', ['monthly', 'yearly', 'lifetime'])->default('monthly');
            $table->json('features')->nullable();
            $table->unsignedInteger('api_rate_limit')->default(0);
            $table->unsignedInteger('pdf_limit')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });

        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['active', 'cancelled', 'expired', 'pending'])->default('pending');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
            $table->index('ends_at');
        });
    }

    private function createApiKeysTable(): void
    {
        Schema::create('api_keys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('key', 64)->unique();
            $table->string('key_prefix', 8);
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedInteger('rate_limit_per_minute')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('key_prefix');
            $table->index('user_id');
        });
    }

    private function createBlogTables(): void
    {
        Schema::create('blog_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('blog_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blog_category_id')->nullable()
                ->constrained('blog_categories')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('reading_time_minutes')->default(1);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('ai_generated')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('published_at');
        });

        Schema::create('blog_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('blog_post_tag', function (Blueprint $table): void {
            $table->foreignId('blog_post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->foreignId('blog_tag_id')->constrained('blog_tags')->cascadeOnDelete();

            $table->unique(['blog_post_id', 'blog_tag_id']);
        });

        Schema::create('blog_post_calculator', function (Blueprint $table): void {
            $table->foreignId('blog_post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->foreignId('calculator_id')->constrained('calculators')->cascadeOnDelete();

            $table->unique(['blog_post_id', 'calculator_id']);
        });
    }

    private function createAdvertisementsTable(): void
    {
        Schema::create('advertisements', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('position', ['header', 'sidebar', 'footer', 'sticky', 'in_content', 'between_results']);
            $table->enum('ad_type', ['adsense', 'banner', 'html', 'affiliate']);
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->string('link_url')->nullable();
            $table->text('adsense_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('position');
            $table->index('is_active');
        });
    }

    private function createSeoPagesTable(): void
    {
        Schema::create('seo_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots')->default('index,follow');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createContactMessagesTable(): void
    {
        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->string('phone')->nullable();
            $table->string('ip_address')->nullable();
            $table->enum('status', ['new', 'read', 'replied', 'archived'])->default('new');
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('email');
        });
    }

    private function createFeedbackTable(): void
    {
        Schema::create('feedback', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('calculator_id')->nullable()->constrained('calculators')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('message');
            $table->enum('type', ['general', 'bug', 'feature', 'calculator'])->default('general');
            $table->enum('status', ['new', 'reviewed', 'resolved'])->default('new');
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('calculator_id');
        });
    }

    private function createSettingsTable(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group');
            $table->string('key');
            $table->longText('value')->nullable();
            $table->string('type')->default('string');
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    private function createAiTables(): void
    {
        Schema::create('ai_prompts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('purpose');
            $table->longText('prompt_template');
            $table->string('model')->nullable();
            $table->string('provider')->nullable();
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->unsignedInteger('max_tokens')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ai_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ai_prompt_id')->nullable()->constrained('ai_prompts')->nullOnDelete();
            $table->string('provider');
            $table->string('model');
            $table->json('request_payload');
            $table->json('response_payload')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->decimal('cost', 10, 6)->nullable();
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->nullableMorphs('aiable');
            $table->timestamps();

            $table->index('status');
            $table->index('provider');
        });
    }

    private function createActivityLogsTable(): void
    {
        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('module');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('module');
            $table->index('action');
            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    private function createPageViewsTable(): void
    {
        Schema::create('page_views', function (Blueprint $table): void {
            $table->id();
            $table->morphs('calculable');
            $table->string('path');
            $table->string('referrer')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('device')->nullable();
            $table->string('ip_hash')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('path');
            $table->index('created_at');
        });
    }

    private function createTranslationsTable(): void
    {
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('locale', 5);
            $table->string('group');
            $table->string('key');
            $table->text('value');
            $table->timestamps();

            $table->unique(['locale', 'group', 'key']);
        });
    }
};
