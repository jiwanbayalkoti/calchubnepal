<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates AJAX "AI Explain" requests sent from a calculator result panel.
 */
class ExplainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $inputs = $this->input('inputs');
        $results = $this->input('results');
        $breakdown = $this->input('breakdown');
        $units = $this->input('units');

        $this->merge([
            'inputs' => $this->decodeArray($inputs),
            'results' => $this->decodeArray($results),
            'breakdown' => $this->decodeArray($breakdown),
            'units' => $this->decodeArray($units),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Empty object {} becomes [] in PHP — do not use `required` on arrays.
            'inputs' => ['nullable', 'array'],
            'results' => ['required', 'array', 'min:1'],
            'breakdown' => ['nullable', 'array'],
            'units' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'results.required' => 'Please calculate a result before requesting an AI explanation.',
            'results.min' => 'Please calculate a result before requesting an AI explanation.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
