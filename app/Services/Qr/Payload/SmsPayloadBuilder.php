<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class SmsPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Sms;
    }

    public function build(array $input): string
    {
        $phone = $this->normalizePhone($this->requireNonEmpty($this->string($input, 'phone'), 'Phone number'));
        $message = $this->string($input, 'message');

        $sms = 'sms:'.$phone;
        if ($message !== '') {
            $sms .= '?body='.rawurlencode($message);
        }

        return $sms;
    }

    public function rules(): array
    {
        return [
            'input.phone' => ['required', 'string', 'min:7', 'max:32', 'regex:/^[\d\s+\-()]+$/'],
            'input.message' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.phone' => 'phone number',
            'input.message' => 'SMS message',
        ];
    }
}
