<?php

namespace App\Services\Ai;

use App\Models\User;

/**
 * Application-facing AI contract. Controllers must depend on this
 * interface - never on a concrete provider or HTTP client - so the
 * underlying AI provider can be swapped via configuration alone.
 */
interface AiServiceInterface
{
    /**
     * Send a raw prompt to the configured (or requested) AI provider and
     * log the request/response to `ai_logs`.
     *
     * @param  array<string, mixed>  $options  provider, model, temperature, max_tokens, ai_prompt_id, subject
     * @return array{content: string, tokens_used: int|null, raw: array<string, mixed>}
     */
    public function generate(string $prompt, array $options = [], ?User $user = null): array;

    /**
     * Render a stored `ai_prompts` template with variables and generate
     * a completion for it.
     *
     * @param  array<string, string|int|float>  $variables
     * @param  array<string, mixed>  $options
     * @return array{content: string, tokens_used: int|null, raw: array<string, mixed>}
     */
    public function generateFromPrompt(string $promptSlug, array $variables = [], array $options = [], ?User $user = null): array;
}
