<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class MecardPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Mecard;
    }

    public function build(array $input): string
    {
        $name = $this->string($input, 'name');
        $phone = $this->string($input, 'phone');
        $email = $this->string($input, 'email');
        $address = $this->string($input, 'address');
        $url = $this->string($input, 'url');
        $note = $this->string($input, 'note');

        if ($name === '' && $phone === '' && $email === '') {
            throw new \InvalidArgumentException('Enter at least a name, phone, or email for MeCard.');
        }

        $parts = ['MECARD:'];
        if ($name !== '') {
            $parts[] = 'N:'.$this->escape($name).';';
        }
        if ($phone !== '') {
            $parts[] = 'TEL:'.$this->normalizePhone($phone).';';
        }
        if ($email !== '') {
            $parts[] = 'EMAIL:'.$this->escape($email).';';
        }
        if ($address !== '') {
            $parts[] = 'ADR:'.$this->escape($address).';';
        }
        if ($url !== '') {
            if (! preg_match('#^https?://#i', $url)) {
                $url = 'https://'.$url;
            }
            $parts[] = 'URL:'.$this->escape($url).';';
        }
        if ($note !== '') {
            $parts[] = 'NOTE:'.$this->escape($note).';';
        }
        $parts[] = ';';

        return implode('', $parts);
    }

    protected function escape(string $value): string
    {
        return str_replace(['\\', ';', ',', ':'], ['\\\\', '\\;', '\\,', '\\:'], $value);
    }

    public function rules(): array
    {
        return [
            'input.name' => ['nullable', 'string', 'max:120'],
            'input.phone' => ['nullable', 'string', 'max:32'],
            'input.email' => ['nullable', 'email', 'max:255'],
            'input.address' => ['nullable', 'string', 'max:255'],
            'input.url' => ['nullable', 'string', 'max:500'],
            'input.note' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.name' => 'name',
            'input.phone' => 'phone',
            'input.email' => 'email',
            'input.address' => 'address',
            'input.url' => 'website',
            'input.note' => 'note',
        ];
    }
}
