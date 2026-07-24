<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class WhatsAppPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::WhatsApp;
    }

    public function build(array $input): string
    {
        $phone = $this->normalizePhone($this->requireNonEmpty($this->string($input, 'phone'), 'WhatsApp number'));
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (strlen($digits) < 8) {
            throw new \InvalidArgumentException('Enter a valid WhatsApp number with country code (digits only preferred).');
        }

        $message = $this->string($input, 'message');
        $url = 'https://wa.me/'.$digits;

        if ($message !== '') {
            $url .= '?text='.rawurlencode($message);
        }

        return $url;
    }

    public function rules(): array
    {
        return [
            'input.phone' => ['required', 'string', 'min:8', 'max:32', 'regex:/^[\d\s+\-()]+$/'],
            'input.message' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.phone' => 'WhatsApp number',
            'input.message' => 'WhatsApp message',
        ];
    }
}
