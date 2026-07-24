<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class EmailPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Email;
    }

    public function build(array $input): string
    {
        $email = $this->requireNonEmpty($this->string($input, 'email'), 'Email');
        $subject = $this->string($input, 'subject');
        $body = $this->string($input, 'body');

        $query = array_filter([
            'subject' => $subject !== '' ? $subject : null,
            'body' => $body !== '' ? $body : null,
        ], static fn ($v) => $v !== null);

        $mailto = 'mailto:'.$email;

        if ($query !== []) {
            $mailto .= '?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        return $mailto;
    }

    public function rules(): array
    {
        return [
            'input.email' => ['required', 'email', 'max:255'],
            'input.subject' => ['nullable', 'string', 'max:255'],
            'input.body' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.email' => 'email address',
            'input.subject' => 'email subject',
            'input.body' => 'email body',
        ];
    }
}
