<?php

namespace App\Services\Ai;

use App\Models\AiLog;
use App\Models\AiPrompt;
use App\Models\User;
use App\Services\Ai\Contracts\AiProviderInterface;
use App\Services\Ai\Providers\GeminiProvider;
use App\Services\Ai\Providers\OpenAiProvider;
use DomainException;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Central AI orchestration service. Controllers/other services must
 * depend on AiServiceInterface and call this class - never a provider or
 * HTTP client directly - so every AI call is uniformly logged to
 * `ai_logs` and providers stay swappable via config/calculator_hub.php.
 */
class AiService implements AiServiceInterface
{
    /**
     * @param  array<string, mixed>  $config  The `calculator_hub.ai` config array.
     */
    public function __construct(protected array $config)
    {
    }

    public function generate(string $prompt, array $options = [], ?User $user = null): array
    {
        $providerName = $options['provider'] ?? $this->config['default'] ?? 'openai';
        $provider = $this->resolveProvider($providerName);

        $options['temperature'] ??= $this->config['default_temperature'] ?? 0.7;
        $options['max_tokens'] ??= $this->config['default_max_tokens'] ?? null;

        $subject = $options['subject'] ?? null;

        $log = AiLog::create([
            'user_id' => $user?->id,
            'ai_prompt_id' => $options['ai_prompt_id'] ?? null,
            'provider' => $provider->name(),
            'model' => $options['model'] ?? $this->config['providers'][$providerName]['model'] ?? 'unknown',
            'request_payload' => ['prompt' => $prompt, 'options' => $this->loggableOptions($options)],
            'status' => AiLog::STATUS_PENDING,
            'aiable_type' => $subject?->getMorphClass(),
            'aiable_id' => $subject?->getKey(),
        ]);

        try {
            $result = $provider->complete($prompt, $options);

            $log->update([
                'response_payload' => $result['raw'] ?? null,
                'tokens_used' => $result['tokens_used'] ?? null,
                'status' => AiLog::STATUS_SUCCESS,
            ]);

            return $result;
        } catch (Throwable $exception) {
            $log->update([
                'status' => AiLog::STATUS_FAILED,
                'error_message' => $exception->getMessage(),
            ]);

            Log::error('AI request failed.', [
                'provider' => $providerName,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function generateFromPrompt(string $promptSlug, array $variables = [], array $options = [], ?User $user = null): array
    {
        $promptModel = AiPrompt::query()
            ->where('slug', $promptSlug)
            ->where('is_active', true)
            ->first();

        if (! $promptModel) {
            throw new DomainException("AI prompt template [{$promptSlug}] was not found or is inactive.");
        }

        $options['ai_prompt_id'] = $promptModel->id;
        $options['provider'] ??= $promptModel->provider;
        $options['model'] ??= $promptModel->model;
        $options['temperature'] ??= (float) $promptModel->temperature;
        $options['max_tokens'] ??= $promptModel->max_tokens;

        return $this->generate($promptModel->render($variables), $options, $user);
    }

    private function resolveProvider(string $name): AiProviderInterface
    {
        $providerConfig = $this->config['providers'][$name] ?? null;

        if ($providerConfig === null) {
            throw new DomainException("Unknown AI provider [{$name}]. Check config/calculator_hub.php.");
        }

        return match ($name) {
            'openai' => new OpenAiProvider($providerConfig),
            'gemini' => new GeminiProvider($providerConfig),
            default => throw new DomainException("AI provider [{$name}] has no registered implementation."),
        };
    }

    /**
     * Strip non-serializable values (e.g. Eloquent models) before logging.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function loggableOptions(array $options): array
    {
        unset($options['subject']);

        return $options;
    }
}
