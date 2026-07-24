<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateBlogWithAiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'instructions' => ['required', 'string', 'min:20', 'max:5000'],
            'title_hint' => ['nullable', 'string', 'max:255'],
            'keyword' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:50'],
            'tone' => ['nullable', 'string', 'max:100'],
            'word_count' => ['nullable', 'integer', 'min:400', 'max:2500'],
            'audience' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:blog_categories,id'],
            'calculator_title' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            // fill = return JSON for form; draft/published = generate + save
            'save_mode' => ['nullable', Rule::in(['fill', 'draft', 'published'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'instructions.required' => 'Please enter instructions for the AI blog.',
            'instructions.min' => 'Instructions should be at least 20 characters.',
        ];
    }
}
