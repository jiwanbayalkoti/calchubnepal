<?php

namespace App\Services\Calculators;

use App\Contracts\Calculators\CalculatorHandlerInterface;
use InvalidArgumentException;

/**
 * Base class providing shared helpers (input casting, rounding, schema
 * validation-rule derivation, field builders) for every concrete
 * calculator handler. Concrete handlers only need to implement key(),
 * inputSchema() and calculate().
 */
abstract class AbstractCalculatorHandler implements CalculatorHandlerInterface
{
    abstract public function key(): string;

    /**
     * @return array<int, array<string, mixed>>
     */
    abstract public function inputSchema(): array;

    /**
     * @param  array<string, mixed>  $inputs
     * @return array{results: array<string, mixed>, breakdown: array<string, mixed>, units: array<string, string>}
     */
    abstract public function calculate(array $inputs): array;

    /**
     * Build Laravel validation rules automatically from the declared
     * input schema so every handler stays consistent without repeating
     * boilerplate rule arrays.
     *
     * @return array<string, array<int, string>>
     */
    public function validationRules(): array
    {
        $rules = [];

        foreach ($this->inputSchema() as $field) {
            $name = $field['name'] ?? null;

            if (! $name) {
                continue;
            }

            $fieldRules = [];
            $required = $field['required'] ?? true;
            $fieldRules[] = $required ? 'required' : 'nullable';

            switch ($field['type'] ?? 'number') {
                case 'number':
                    $fieldRules[] = 'numeric';
                    if (isset($field['min'])) {
                        $fieldRules[] = 'min:'.$field['min'];
                    }
                    if (isset($field['max'])) {
                        $fieldRules[] = 'max:'.$field['max'];
                    }
                    break;

                case 'integer':
                    $fieldRules[] = 'integer';
                    if (isset($field['min'])) {
                        $fieldRules[] = 'min:'.$field['min'];
                    }
                    if (isset($field['max'])) {
                        $fieldRules[] = 'max:'.$field['max'];
                    }
                    break;

                case 'select':
                case 'radio':
                    $fieldRules[] = 'string';
                    if (! empty($field['options']) && is_array($field['options'])) {
                        $fieldRules[] = 'in:'.implode(',', array_keys($field['options']));
                    }
                    break;

                case 'boolean':
                    $fieldRules[] = 'boolean';
                    break;

                case 'date':
                    $fieldRules[] = 'date';
                    break;

                case 'time':
                    $fieldRules[] = 'date_format:H:i';
                    break;

                case 'array':
                    $fieldRules[] = 'array';
                    break;

                default:
                    $fieldRules[] = 'string';
                    if (isset($field['max_length'])) {
                        $fieldRules[] = 'max:'.$field['max_length'];
                    }
                    break;
            }

            $rules[$name] = $fieldRules;
        }

        return $rules;
    }

    /**
     * Build a normalized input-schema field definition.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function field(string $name, string $label, string $type = 'number', array $extra = []): array
    {
        return array_merge([
            'name' => $name,
            'label' => $label,
            'type' => $type,
            'unit' => null,
            'required' => true,
        ], $extra);
    }

    protected function round(float $value, int $precision = 2): float
    {
        if (! is_finite($value)) {
            return 0.0;
        }

        return round($value, $precision);
    }

    /**
     * @param  array<string, mixed>  $inputs
     */
    protected function toFloat(array $inputs, string $key, float $default = 0.0): float
    {
        if (! isset($inputs[$key]) || $inputs[$key] === '' || $inputs[$key] === null) {
            return $default;
        }

        return (float) $inputs[$key];
    }

    /**
     * @param  array<string, mixed>  $inputs
     */
    protected function toInt(array $inputs, string $key, int $default = 0): int
    {
        if (! isset($inputs[$key]) || $inputs[$key] === '' || $inputs[$key] === null) {
            return $default;
        }

        return (int) $inputs[$key];
    }

    /**
     * @param  array<string, mixed>  $inputs
     */
    protected function toString(array $inputs, string $key, string $default = ''): string
    {
        if (! isset($inputs[$key]) || $inputs[$key] === null) {
            return $default;
        }

        return (string) $inputs[$key];
    }

    /**
     * @param  array<string, mixed>  $inputs
     */
    protected function toBool(array $inputs, string $key, bool $default = false): bool
    {
        if (! isset($inputs[$key]) || $inputs[$key] === null || $inputs[$key] === '') {
            return $default;
        }

        return filter_var($inputs[$key], FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param  array<string, mixed>  $inputs
     */
    protected function toArray(array $inputs, string $key, array $default = []): array
    {
        if (! isset($inputs[$key]) || ! is_array($inputs[$key])) {
            return $default;
        }

        return $inputs[$key];
    }

    /**
     * Fetch a required numeric input, throwing when missing or non numeric.
     *
     * @param  array<string, mixed>  $inputs
     */
    protected function requireNumeric(array $inputs, string $key): float
    {
        if (! isset($inputs[$key]) || $inputs[$key] === '' || ! is_numeric($inputs[$key])) {
            throw new InvalidArgumentException("The field [{$key}] is required and must be numeric.");
        }

        return (float) $inputs[$key];
    }

    protected function percentageOf(float $part, float $whole): float
    {
        return $whole == 0.0 ? 0.0 : ($part / $whole) * 100;
    }

    protected function safeDivide(float $numerator, float $denominator, float $fallback = 0.0): float
    {
        return $denominator == 0.0 ? $fallback : $numerator / $denominator;
    }
}
