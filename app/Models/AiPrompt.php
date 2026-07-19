<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiPrompt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'purpose',
        'prompt_template',
        'model',
        'provider',
        'temperature',
        'max_tokens',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:2',
            'max_tokens' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AiLog::class);
    }

    /**
     * Resolve the prompt template by replacing `{{placeholder}}` tokens.
     *
     * @param  array<string, string|int|float>  $variables
     */
    public function render(array $variables = []): string
    {
        $template = (string) $this->prompt_template;

        foreach ($variables as $key => $value) {
            $template = str_replace('{{'.$key.'}}', (string) $value, $template);
        }

        return $template;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
