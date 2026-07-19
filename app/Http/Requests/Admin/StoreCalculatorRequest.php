<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCalculatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('create', \App\Models\Calculator::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'calculator_category_id' => ['required', 'integer', 'exists:calculator_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('calculators', 'slug')],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'formula_key' => ['required', 'string', 'max:255'],
            'formula_expression' => ['nullable', 'string'],
            'formula_description' => ['nullable', 'string'],
            'input_schema' => ['required', 'json'],
            'validation_rules' => ['nullable', 'json'],
            'result_schema' => ['nullable', 'json'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'string', 'max:255'],
            'is_premium' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'input_schema.json' => 'The input schema must be valid JSON.',
            'validation_rules.json' => 'The validation rules must be valid JSON.',
            'result_schema.json' => 'The result schema must be valid JSON.',
        ];
    }
}
