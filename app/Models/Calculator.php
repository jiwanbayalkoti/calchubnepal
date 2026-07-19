<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calculator extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'calculator_category_id',
        'title',
        'slug',
        'short_description',
        'description',
        'icon',
        'formula_key',
        'formula_expression',
        'formula_description',
        'input_schema',
        'validation_rules',
        'result_schema',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'canonical_url',
        'is_premium',
        'is_featured',
        'is_active',
        'views_count',
        'usage_count',
        'sort_order',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input_schema' => 'array',
            'validation_rules' => 'array',
            'result_schema' => 'array',
            'is_premium' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'views_count' => 'integer',
            'usage_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CalculatorCategory::class, 'calculator_category_id');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(CalculatorFaq::class)->orderBy('sort_order');
    }

    public function examples(): HasMany
    {
        return $this->hasMany(CalculatorExample::class)->orderBy('sort_order');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(CalculationHistory::class);
    }

    public function savedCalculations(): HasMany
    {
        return $this->hasMany(SavedCalculation::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(CalculatorFavorite::class);
    }

    /**
     * Calculators explicitly linked as "related" to this calculator.
     */
    public function relatedCalculators(): BelongsToMany
    {
        return $this->belongsToMany(
            Calculator::class,
            'calculator_relations',
            'calculator_id',
            'related_calculator_id'
        )->withPivot('sort_order')->orderBy('calculator_relations.sort_order');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopePremium(Builder $query): Builder
    {
        return $query->where('is_premium', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }
}
