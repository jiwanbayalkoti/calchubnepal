<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('rating') === '' || $this->input('rating') === null) {
            $this->merge(['rating' => null]);
        }

        if ($this->input('calculator_id') === '' || $this->input('calculator_id') === null) {
            $this->merge(['calculator_id' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:5000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'type' => ['nullable', Rule::in(['general', 'bug', 'feature', 'calculator'])],
            'calculator_id' => ['nullable', 'integer', 'exists:calculators,id'],
        ];
    }
}
