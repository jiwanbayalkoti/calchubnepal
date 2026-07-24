<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class VcardPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Vcard;
    }

    public function build(array $input): string
    {
        $first = $this->string($input, 'first_name');
        $last = $this->string($input, 'last_name');
        $org = $this->string($input, 'organization');
        $title = $this->string($input, 'title');
        $phone = $this->string($input, 'phone');
        $email = $this->string($input, 'email');
        $url = $this->string($input, 'url');
        $address = $this->string($input, 'address');

        if ($first === '' && $last === '' && $org === '') {
            throw new \InvalidArgumentException('Enter at least a name or organization for the vCard.');
        }

        $fullName = trim($first.' '.$last);
        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'N:'.$last.';'.$first.';;;',
            'FN:'.($fullName !== '' ? $fullName : $org),
        ];

        if ($org !== '') {
            $lines[] = 'ORG:'.$org;
        }
        if ($title !== '') {
            $lines[] = 'TITLE:'.$title;
        }
        if ($phone !== '') {
            $lines[] = 'TEL;TYPE=CELL:'.$this->normalizePhone($phone);
        }
        if ($email !== '') {
            $lines[] = 'EMAIL:'.$email;
        }
        if ($url !== '') {
            if (! preg_match('#^https?://#i', $url)) {
                $url = 'https://'.$url;
            }
            $lines[] = 'URL:'.$url;
        }
        if ($address !== '') {
            $lines[] = 'ADR:;;'.$address.';;;;';
        }

        $lines[] = 'END:VCARD';

        return implode("\n", $lines);
    }

    public function rules(): array
    {
        return [
            'input.first_name' => ['nullable', 'string', 'max:100'],
            'input.last_name' => ['nullable', 'string', 'max:100'],
            'input.organization' => ['nullable', 'string', 'max:150'],
            'input.title' => ['nullable', 'string', 'max:100'],
            'input.phone' => ['nullable', 'string', 'max:32'],
            'input.email' => ['nullable', 'email', 'max:255'],
            'input.url' => ['nullable', 'string', 'max:500'],
            'input.address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.first_name' => 'first name',
            'input.last_name' => 'last name',
            'input.organization' => 'organization',
            'input.title' => 'job title',
            'input.phone' => 'phone',
            'input.email' => 'email',
            'input.url' => 'website',
            'input.address' => 'address',
        ];
    }
}
