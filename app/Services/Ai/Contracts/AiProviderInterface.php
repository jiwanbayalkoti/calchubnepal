<?php

namespace App\Services\Ai\Contracts;

/**
 * Contract every AI provider adapter (OpenAI, Gemini, Claude, ...) must
 * implement. Providers are plain config-driven HTTP adapters - no
 * business logic, prompt storage, or logging belongs here; that lives in
 * AiService.
 */
interface AiProviderInterface
{
    /**
     * The provider's config/registry key, e.g. "openai", "gemini".
     */
    public function name(): string;

    /**
     * Send a prompt to the provider and return a normalized response.
     *
     * @param  array<string, mixed>  $options  model, temperature, max_tokens, ...
     * @return array{content: string, tokens_used: int|null, raw: array<string, mixed>}
     */
    public function complete(string $prompt, array $options = []): array;
}
