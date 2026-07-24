<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class UrlPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Url;
    }

    public function build(array $input): string
    {
        $url = $this->requireNonEmpty($this->string($input, 'url'), 'Website URL');

        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        return $url;
    }

    public function rules(): array
    {
        return [
            'input.url' => ['required', 'string', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return ['input.url' => 'website URL'];
    }
}
