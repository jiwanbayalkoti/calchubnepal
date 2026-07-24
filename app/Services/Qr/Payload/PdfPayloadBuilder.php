<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class PdfPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Pdf;
    }

    public function build(array $input): string
    {
        $url = $this->requireNonEmpty($this->string($input, 'url'), 'PDF / file URL');
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
        return ['input.url' => 'PDF / file URL'];
    }
}
