<?php

namespace App\Contracts\Calculators;

/**
 * Contract implemented by every calculator formula handler.
 *
 * Each handler encapsulates the business logic (formula) for a single
 * calculator and is fully decoupled from HTTP, persistence, and validation
 * concerns. Handlers are registered in the CalculatorRegistry and resolved
 * by the "formula_key" stored on the Calculator model.
 */
interface CalculatorHandlerInterface
{
    /**
     * Unique key identifying this handler. Must match the "formula_key"
     * column of the related Calculator record (e.g. "brick_calculator").
     */
    public function key(): string;

    /**
     * Run the calculation for the given user inputs.
     *
     * The returned array must always contain the following keys:
     * - results:   associative array of the primary output values.
     * - breakdown: associative array of step-by-step / intermediate values
     *              useful for displaying a transparent calculation trail.
     * - units:     associative array mapping result/breakdown keys to a
     *              human readable unit label (e.g. "kg", "%", "years").
     *
     * @param  array<string, mixed>  $inputs
     * @return array{results: array<string, mixed>, breakdown: array<string, mixed>, units: array<string, string>}
     */
    public function calculate(array $inputs): array;

    /**
     * Laravel validation rules derived from the input schema, used to
     * validate incoming requests before calculate() is invoked.
     *
     * @return array<string, array<int, string>|string>
     */
    public function validationRules(): array;

    /**
     * Machine readable description of every input field accepted by this
     * handler. Consumed by the front-end to render the calculator form and
     * by the API to document the calculator.
     *
     * Each entry contains: name, label, type, unit, min, max, step,
     * required, default (as applicable to the field type).
     *
     * @return array<int, array<string, mixed>>
     */
    public function inputSchema(): array;
}
