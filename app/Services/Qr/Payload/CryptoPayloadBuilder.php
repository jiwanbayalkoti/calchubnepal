<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class CryptoPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Crypto;
    }

    public function build(array $input): string
    {
        $coin = strtolower($this->string($input, 'coin', 'bitcoin'));
        $address = $this->requireNonEmpty($this->string($input, 'address'), 'Wallet address');
        $amount = $this->string($input, 'amount');
        $label = $this->string($input, 'label');
        $message = $this->string($input, 'message');

        if ($coin === 'bitcoin' || $coin === 'btc') {
            $uri = 'bitcoin:'.$address;
            $params = [];
            if ($amount !== '' && is_numeric($amount)) {
                $params[] = 'amount='.rawurlencode($amount);
            }
            if ($label !== '') {
                $params[] = 'label='.rawurlencode($label);
            }
            if ($message !== '') {
                $params[] = 'message='.rawurlencode($message);
            }

            return $params === [] ? $uri : $uri.'?'.implode('&', $params);
        }

        if ($coin === 'ethereum' || $coin === 'eth') {
            $uri = 'ethereum:'.$address;
            if ($amount !== '' && is_numeric($amount)) {
                $uri .= '?value='.rawurlencode($amount);
            }

            return $uri;
        }

        // Generic: readable text + address for other coins
        $lines = [strtoupper($coin).' WALLET', 'Address: '.$address];
        if ($amount !== '') {
            $lines[] = 'Amount: '.$amount;
        }
        if ($label !== '') {
            $lines[] = 'Label: '.$label;
        }

        return implode("\n", $lines);
    }

    public function rules(): array
    {
        return [
            'input.coin' => ['nullable', 'string', 'in:bitcoin,btc,ethereum,eth,usdt,other'],
            'input.address' => ['required', 'string', 'max:200'],
            'input.amount' => ['nullable', 'string', 'max:40'],
            'input.label' => ['nullable', 'string', 'max:80'],
            'input.message' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.coin' => 'cryptocurrency',
            'input.address' => 'wallet address',
            'input.amount' => 'amount',
            'input.label' => 'label',
            'input.message' => 'message',
        ];
    }
}
