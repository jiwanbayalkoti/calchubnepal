<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class KhaltiPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Khalti;
    }

    public function build(array $input): string
    {
        $id = $this->requireNonEmpty($this->string($input, 'khalti_id'), 'Khalti ID / mobile');
        $name = $this->string($input, 'name');
        $amount = $this->string($input, 'amount');
        $purpose = $this->string($input, 'purpose');
        $url = $this->string($input, 'url');

        if ($url !== '') {
            if (! preg_match('#^https?://#i', $url)) {
                $url = 'https://'.$url;
            }

            return $url;
        }

        $lines = [
            'Khalti Payment',
            'Khalti ID: '.$id,
        ];
        if ($name !== '') {
            $lines[] = 'Name: '.$name;
        }
        if ($amount !== '') {
            $lines[] = 'Amount (NPR): '.$amount;
        }
        if ($purpose !== '') {
            $lines[] = 'Purpose: '.$purpose;
        }
        $lines[] = 'Open Khalti app → Send Money → enter this ID.';

        return implode("\n", $lines);
    }

    public function rules(): array
    {
        return [
            'input.khalti_id' => ['required', 'string', 'max:64'],
            'input.name' => ['nullable', 'string', 'max:120'],
            'input.amount' => ['nullable', 'string', 'max:40'],
            'input.purpose' => ['nullable', 'string', 'max:120'],
            'input.url' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.khalti_id' => 'Khalti ID',
            'input.name' => 'account name',
            'input.amount' => 'amount',
            'input.purpose' => 'purpose',
            'input.url' => 'payment URL',
        ];
    }
}
