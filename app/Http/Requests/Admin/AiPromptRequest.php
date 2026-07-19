<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiPromptRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('ai_prompts', 'slug')->ignore($this->route('id'))],
            'purpose' => ['required', 'string', 'max:255'],
            'prompt_template' => ['required', 'string'],
            'model' => ['nullable', 'string', 'max:255'],
            'provider' => ['nullable', Rule::in(['openai', 'gemini', 'claude'])],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
