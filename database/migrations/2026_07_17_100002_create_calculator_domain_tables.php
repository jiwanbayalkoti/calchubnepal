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
        Schema::create('calculator_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('sort_order');
        });

        Schema::create('calculators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculator_category_id')->constrained('calculator_categories')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('formula_key');
            $table->text('formula_expression')->nullable();
            $table->text('formula_description')->nullable();
            $table->json('input_schema');
            $table->json('validation_rules')->nullable();
            $table->json('result_schema')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('usage_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('calculator_category_id', 'idx_calculators_category');
            $table->index('is_active', 'idx_calculators_active');
            $table->index('is_featured', 'idx_calculators_featured');
            $table->index('formula_key', 'idx_calculators_formula_key');
            $table->index('views_count', 'idx_calculators_views_count');
        });

        Schema::create('calculator_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculator_id')->constrained('calculators')->cascadeOnDelete();
            $table->string('question');
            $table->longText('answer');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['calculator_id', 'sort_order'], 'idx_calculator_faqs_calc_sort');
        });

        Schema::create('calculator_examples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculator_id')->constrained('calculators')->cascadeOnDelete();
            $table->string('title');
            $table->json('inputs');
            $table->json('outputs');
            $table->text('explanation')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['calculator_id', 'sort_order'], 'idx_calculator_examples_calc_sort');
        });

        Schema::create('calculator_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculator_id')->constrained('calculators')->cascadeOnDelete();
            $table->foreignId('related_calculator_id')->constrained('calculators')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['calculator_id', 'related_calculator_id'], 'uniq_calculator_relations_pair');
        });

        Schema::create('calculation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('calculator_id')->constrained('calculators')->cascadeOnDelete();
            $table->json('inputs');
            $table->json('outputs');
            $table->longText('explanation')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'created_at'], 'idx_calc_histories_user_created');
            $table->index(['calculator_id', 'created_at'], 'idx_calc_histories_calc_created');
        });

        Schema::create('saved_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('calculator_id')->constrained('calculators')->cascadeOnDelete();
            $table->string('title');
            $table->json('inputs');
            $table->json('outputs');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'calculator_id'], 'idx_saved_calculations_user_calc');
        });

        Schema::create('calculator_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('calculator_id')->constrained('calculators')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'calculator_id'], 'uniq_calculator_favorites_user_calc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculator_favorites');
        Schema::dropIfExists('saved_calculations');
        Schema::dropIfExists('calculation_histories');
        Schema::dropIfExists('calculator_relations');
        Schema::dropIfExists('calculator_examples');
        Schema::dropIfExists('calculator_faqs');
        Schema::dropIfExists('calculators');
        Schema::dropIfExists('calculator_categories');
    }
};
