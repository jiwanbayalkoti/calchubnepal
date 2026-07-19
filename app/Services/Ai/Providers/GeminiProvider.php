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

        $response = Http::timeout((int) ($this->config['timeout'] ?? 30))
            ->post("{$baseUrl}/models/{$model}:generateContent?key={$apiKey}", array_filter([
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => array_filter([
                    'temperature' => $options['temperature'] ?? 0.7,
                    'maxOutputTokens' => $options['max_tokens'] ?? null,
                ], static fn ($value) => $value !== null),
            ]));

        if ($response->failed()) {
            throw new RuntimeException('Gemini request failed: '.$response->body());
        }

        $data = $response->json() ?? [];

        return [
            'content' => (string) ($data['candidates'][0]['content']['parts'][0]['text'] ?? ''),
            'tokens_used' => $data['usageMetadata']['totalTokenCount'] ?? null,
            'raw' => $data,
        ];
    }
}
