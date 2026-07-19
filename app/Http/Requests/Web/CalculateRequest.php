<?php

namespace App\Http\Requests\Web;

use App\Contracts\Services\CalculatorServiceInterface;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates AJAX calculation submissions. Rules are derived dynamically
 * from the target calculator's handler input schema so every calculator
 * stays validated without duplicating rule arrays per form request.
 */
class CalculateRequest extends FormRequest
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
        $slug = (string) $this->route('calculator');

        /** @var CalculatorServiceInterface $service */
        $service = app(CalculatorServiceInterface::class);

        try {
            $calculator = $service->getBySlug($slug);
        } catch (\Throwable) {
            return [];
        }

        $registry = app(\App\Services\Calculators\CalculatorRegistry::class);

        if (! $registry->has($calculator->formula_key)) {
            return [];
        }

        return $registry->get($calculator->formula_key)->validationRules();
    }

    public function messages(): array
    {
        return [
            'required' => 'This field is required.',
            'numeric' => 'Please enter a valid number.',
            'min' => 'The value is too small.',
            'max' => 'The value is too large.',
        ];
    }
}
