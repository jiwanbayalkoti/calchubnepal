<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class TextPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Text;
    }

    public function build(array $input): string
    {
        return $this->requireNonEmpty($this->string($input, 'text'), 'Text');
    }

    public function rules(): array
    {
        return [
            'input.text' => ['required', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return ['input.text' => 'text'];
    }
}
