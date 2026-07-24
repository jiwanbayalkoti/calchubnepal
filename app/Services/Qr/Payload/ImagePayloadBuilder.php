<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class ImagePayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Image;
    }

    public function build(array $input): string
    {
        $url = $this->requireNonEmpty($this->string($input, 'url'), 'Image URL');
        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        return $url;
    }

    public function rules(): array
    {
        return [
            'input.url' => ['required', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return ['input.url' => 'image URL'];
    }
}
