<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class ViberPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Viber;
    }

    public function build(array $input): string
    {
        $phone = preg_replace('/\D/', '', $this->requireNonEmpty($this->string($input, 'phone'), 'Viber number')) ?? '';
        if (strlen($phone) < 8) {
            throw new \InvalidArgumentException('Enter a valid Viber number with country code.');
        }
        $message = $this->string($input, 'message');
        $url = 'viber://chat?number=%2B'.$phone;
        if ($message !== '') {
            $url .= '&draft='.rawurlencode($message);
        }

        return $url;
    }

    public function rules(): array
    {
        return [
            'input.phone' => ['required', 'string', 'min:8', 'max:32'],
            'input.message' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.phone' => 'Viber number',
            'input.message' => 'message',
        ];
    }
}
