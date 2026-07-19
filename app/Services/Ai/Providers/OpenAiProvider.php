<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\AiProviderInterface;
use DomainException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiProvider implements AiProviderInterface
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(protected array $config)
    {
    }

    public function name(): string
    {
        return 'openai';
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
                'OpenAI API key is not configured. Set OPENAI_API_KEY in your .env file.'
            );
        }

        $model = $options['model'] ?? $this->config['model'] ?? 'gpt-4o-mini';
        $baseUrl = rtrim($this->config['base_url'] ?? 'https://api.openai.com/v1', '/');

        $response = Http::withToken($apiKey)
            ->timeout((int) ($this->config['timeout'] ?? 30))
            ->post("{$baseUrl}/chat/completions", array_filter([
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? null,
            ], static fn ($value) => $value !== null));

        if ($response->failed()) {
            throw new RuntimeException('OpenAI request failed: '.$response->body());
        }

        $data = $response->json() ?? [];

        return [
            'content' => (string) ($data['choices'][0]['message']['content'] ?? ''),
            'tokens_used' => $data['usage']['total_tokens'] ?? null,
            'raw' => $data,
        ];
    }
}
