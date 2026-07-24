<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class MusicPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Music;
    }

    public function build(array $input): string
    {
        $url = $this->requireNonEmpty($this->string($input, 'url'), 'Music / playlist URL');
        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        return $url;
    }

    public function rules(): array
    {
        return [
            'input.platform' => ['nullable', 'string', 'in:spotify,youtube,apple,other'],
            'input.url' => ['required', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.platform' => 'platform',
            'input.url' => 'music URL',
        ];
    }
}
