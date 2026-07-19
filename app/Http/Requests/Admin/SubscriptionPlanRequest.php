<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriptionPlanRequest extends FormRequest
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
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('subscription_plans', 'slug')->ignore($this->route('id'))],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:3'],
            'billing_period' => ['required', Rule::in(['monthly', 'yearly', 'lifetime'])],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            'api_rate_limit' => ['nullable', 'integer', 'min:0'],
            'pdf_limit' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $features = $this->input('features', $this->input('features_text'));

        if (is_string($features)) {
            $trimmed = trim($features);

            if ($trimmed === '') {
                $features = [];
            } elseif (str_starts_with($trimmed, '[')) {
                $decoded = json_decode($trimmed, true);
                $features = is_array($decoded)
                    ? array_values(array_filter(array_map(
                        static fn ($item) => is_string($item) ? trim($item) : $item,
                        $decoded
                    )))
                    : [];
            } else {
                $features = array_values(array_filter(array_map(
                    static fn (string $item): string => trim($item),
                    explode(',', $features)
                ), static fn (string $item): bool => $item !== ''));
            }
        }

        if (! is_array($features)) {
            $features = [];
        }

        $merge = ['features' => $features];

        if ($this->has('is_active')) {
            $merge['is_active'] = filter_var(
                $this->input('is_active'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ?? false;
        }

        $this->merge($merge);
    }
}
