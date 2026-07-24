<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class UpiPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Upi;
    }

    public function build(array $input): string
    {
        $pa = $this->requireNonEmpty($this->string($input, 'pa'), 'UPI ID (VPA)');
        $pn = $this->string($input, 'pn', 'Payee');
        $am = $this->string($input, 'am');
        $cu = $this->string($input, 'cu', 'INR');
        $tn = $this->string($input, 'tn');

        $params = [
            'pa='.rawurlencode($pa),
            'pn='.rawurlencode($pn),
            'cu='.rawurlencode($cu !== '' ? $cu : 'INR'),
        ];
        if ($am !== '' && is_numeric($am)) {
            $params[] = 'am='.rawurlencode(number_format((float) $am, 2, '.', ''));
        }
        if ($tn !== '') {
            $params[] = 'tn='.rawurlencode($tn);
        }

        return 'upi://pay?'.implode('&', $params);
    }

    public function rules(): array
    {
        return [
            'input.pa' => ['required', 'string', 'max:120'],
            'input.pn' => ['nullable', 'string', 'max:80'],
            'input.am' => ['nullable', 'string', 'max:20'],
            'input.cu' => ['nullable', 'string', 'max:8'],
            'input.tn' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.pa' => 'UPI ID',
            'input.pn' => 'payee name',
            'input.am' => 'amount',
            'input.cu' => 'currency',
            'input.tn' => 'note',
        ];
    }
}
