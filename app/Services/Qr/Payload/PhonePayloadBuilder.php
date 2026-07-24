<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class PhonePayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Phone;
    }

    public function build(array $input): string
    {
        $phone = $this->normalizePhone($this->requireNonEmpty($this->string($input, 'phone'), 'Phone number'));

        if (strlen(preg_replace('/\D/', '', $phone) ?? '') < 7) {
            throw new \InvalidArgumentException('Enter a valid phone number.');
        }

        return 'tel:'.$phone;
    }

    public function rules(): array
    {
        return [
            'input.phone' => ['required', 'string', 'min:7', 'max:32', 'regex:/^[\d\s+\-()]+$/'],
        ];
    }

    public function attributes(): array
    {
        return ['input.phone' => 'phone number'];
    }
}
