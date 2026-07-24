<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\AiProviderInterface;
use DomainException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProvider implements AiProviderInterface
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(protected array $config)
    {
    }

    public function name(): string
    {
        return 'gemini';
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{content: string, tokens_used: int|null, raw: array<string, mixed>}
     */
    public function complete(string $prompt, array $options = []): array
    {
        $apiKey = $this->config['api_key'] ?? null;

        if (blank($apiKey)) {
            throw new DomainException(
                'Gemini API key is not configured. Set GEMINI_API_KEY in your .env file.'
            );
        }

        $model = $options['model'] ?? $this->config['model'] ?? 'gemini-1.5-flash';
        $baseUrl = rtrim($this->config['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta', '/');

        $generationConfig = array_filter([
            'temperature' => $options['temperature'] ?? 0.7,
            'maxOutputTokens' => $options['max_tokens'] ?? null,
        ], static fn ($value) => $value !== null);

        // Thinking models (gemini-flash-latest) can burn most of maxOutputTokens on
        // thoughts and truncate the blog JSON mid-sentence. Cap thinking budget.
        // Note: thinkingBudget=0 is rejected by the API for some models.
        if (! empty($options['disable_thinking'])) {
            $generationConfig['thinkingConfig'] = [
                'thinkingBudget' => (int) ($options['thinking_budget'] ?? 512),
            ];
        }

        $response = Http::timeout((int) ($this->config['timeout'] ?? 30))
            ->post("{$baseUrl}/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => $generationConfig,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Gemini request failed: '.$response->body());
        }

        $data = $response->json() ?? [];
        $parts = $data['candidates'][0]['content']['parts'] ?? [];
        $content = '';
        foreach ($parts as $part) {
            if (! empty($part['text'])) {
                $content .= (string) $part['text'];
            }
        }

        return [
            'content' => $content,
            'tokens_used' => $data['usageMetadata']['totalTokenCount'] ?? null,
            'raw' => $data,
        ];
    }
}
