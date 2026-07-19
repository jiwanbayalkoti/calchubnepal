<?php

namespace App\Services\Ai;

/**
 * Builds a clear, human-readable explanation from calculator inputs/results
 * when no external AI provider API key is configured (local/dev fallback).
 */
class LocalExplanationBuilder
{
    /**
     * @param  array<string, mixed>  $inputs
     * @param  array<string, mixed>  $results
     * @param  array<string, mixed>  $breakdown
     * @param  array<string, mixed>  $units
     */
    public function build(
        string $calculatorTitle,
        array $inputs,
        array $results,
        array $breakdown = [],
        array $units = [],
        ?string $formulaDescription = null,
    ): string {
        $parts = [];

        $parts[] = "Here's how your {$calculatorTitle} result was calculated.";

        if ($formulaDescription) {
            $parts[] = trim($formulaDescription);
        }

        if ($inputs !== []) {
            $parts[] = 'You entered: '.$this->formatPairs($inputs, $units).'.';
        }

        if ($results !== []) {
            $parts[] = 'That produced: '.$this->formatPairs($results, $units).'.';
        }

        if ($breakdown !== []) {
            $parts[] = 'Step details: '.$this->formatPairs($breakdown, $units).'.';
        }

        $parts[] = 'Tip: add an OPENAI_API_KEY or GEMINI_API_KEY in your .env file to enable richer AI-written explanations.';

        return implode("\n\n", $parts);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $units
     */
    private function formatPairs(array $data, array $units): string
    {
        $chunks = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            $label = $this->humanize((string) $key);
            $unit = isset($units[$key]) ? ' '.$units[$key] : '';
            $chunks[] = "{$label} = {$value}{$unit}";
        }

        return implode('; ', $chunks);
    }

    private function humanize(string $key): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $key));
    }
}
