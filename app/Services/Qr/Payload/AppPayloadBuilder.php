<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class AppPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::App;
    }

    public function build(array $input): string
    {
        $store = strtolower($this->string($input, 'store', 'auto'));
        $ios = $this->string($input, 'ios_url');
        $android = $this->string($input, 'android_url');
        $url = $this->string($input, 'url');

        if ($url !== '') {
            return $this->ensureHttp($url);
        }

        return match ($store) {
            'ios' => $this->ensureHttp($this->requireNonEmpty($ios, 'App Store URL')),
            'android' => $this->ensureHttp($this->requireNonEmpty($android, 'Play Store URL')),
            default => $this->ensureHttp(
                $this->requireNonEmpty($ios !== '' ? $ios : $android, 'App Store or Play Store URL')
            ),
        };
    }

    protected function ensureHttp(string $url): string
    {
        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        return $url;
    }

    public function rules(): array
    {
        return [
            'input.store' => ['nullable', 'string', 'in:auto,ios,android'],
            'input.ios_url' => ['nullable', 'string', 'max:500'],
            'input.android_url' => ['nullable', 'string', 'max:500'],
            'input.url' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.store' => 'app store',
            'input.ios_url' => 'App Store URL',
            'input.android_url' => 'Play Store URL',
            'input.url' => 'app URL',
        ];
    }
}
