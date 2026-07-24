<?php

namespace App\Services\Qr\Payload;

use App\Contracts\Qr\QrPayloadBuilderInterface;
use App\Enums\Qr\QrType;
use InvalidArgumentException;

abstract class AbstractQrPayloadBuilder implements QrPayloadBuilderInterface
{
    /**
     * @param  array<string, mixed>  $input
     */
    protected function string(array $input, string $key, string $default = ''): string
    {
        $value = $input[$key] ?? $default;

        return is_scalar($value) ? trim((string) $value) : $default;
    }

    protected function requireNonEmpty(string $value, string $label): string
    {
        if ($value === '') {
            throw new InvalidArgumentException("{$label} is required.");
        }

        return $value;
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^\d+]/', '', $phone) ?? '';
        $phone = preg_replace('/(?!^)\+/', '', $phone) ?? $phone;

        return $phone;
    }

    abstract public function type(): QrType;

    abstract public function build(array $input): string;

    abstract public function rules(): array;

    public function attributes(): array
    {
        return [];
    }
}
