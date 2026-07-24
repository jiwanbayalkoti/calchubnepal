<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class TelegramPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Telegram;
    }

    public function build(array $input): string
    {
        $username = ltrim($this->string($input, 'username'), '@/');
        $phone = preg_replace('/\D/', '', $this->string($input, 'phone')) ?? '';
        $message = $this->string($input, 'message');

        if ($username !== '') {
            $url = 'https://t.me/'.$username;
            if ($message !== '') {
                $url .= '?text='.rawurlencode($message);
            }

            return $url;
        }

        if (strlen($phone) >= 8) {
            $url = 'https://t.me/+'.$phone;
            // Phone share via tg deep link is limited; encode as contact hint text fallback
            return $message !== ''
                ? 'https://t.me/share/url?url='.rawurlencode($message)
                : 'tg://resolve?phone='.$phone;
        }

        throw new \InvalidArgumentException('Enter a Telegram username or phone number.');
    }

    public function rules(): array
    {
        return [
            'input.username' => ['nullable', 'string', 'max:64'],
            'input.phone' => ['nullable', 'string', 'max:32'],
            'input.message' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.username' => 'Telegram username',
            'input.phone' => 'phone',
            'input.message' => 'message',
        ];
    }
}
