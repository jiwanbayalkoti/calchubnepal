<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;
use App\Services\Qr\Support\EmvcoPayload;

class NepalQrPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::NepalQr;
    }

    public function build(array $input): string
    {
        $merchantId = $this->requireNonEmpty($this->string($input, 'merchant_id'), 'Merchant / account ID');
        $name = $this->requireNonEmpty($this->string($input, 'merchant_name'), 'Merchant / account name');

        return EmvcoPayload::buildNepalStatic([
            'merchant_id' => $merchantId,
            'merchant_name' => $name,
            'city' => $this->string($input, 'city', 'Kathmandu'),
            'amount' => $this->string($input, 'amount'),
            'guid' => $this->string($input, 'guid', 'np.com.fonepay') ?: 'np.com.fonepay',
            'mcc' => $this->string($input, 'mcc', '0000') ?: '0000',
        ]);
    }

    public function rules(): array
    {
        return [
            'input.merchant_id' => ['required', 'string', 'max:32'],
            'input.merchant_name' => ['required', 'string', 'max:25'],
            'input.city' => ['nullable', 'string', 'max:15'],
            'input.amount' => ['nullable', 'string', 'max:20'],
            'input.guid' => ['nullable', 'string', 'max:50'],
            'input.mcc' => ['nullable', 'string', 'max:4'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.merchant_id' => 'merchant ID',
            'input.merchant_name' => 'merchant name',
            'input.city' => 'city',
            'input.amount' => 'amount',
            'input.guid' => 'acquirer GUID',
            'input.mcc' => 'MCC',
        ];
    }
}
