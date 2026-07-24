<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class MessengerPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Messenger;
    }

    public function build(array $input): string
    {
        $username = ltrim($this->string($input, 'username'), '@/');
        $pageId = $this->string($input, 'page_id');
        $url = $this->string($input, 'url');

        if ($url !== '') {
            if (! preg_match('#^https?://#i', $url)) {
                $url = 'https://'.$url;
            }

            return $url;
        }

        if ($username !== '') {
            return 'https://m.me/'.$username;
        }

        if ($pageId !== '') {
            return 'https://m.me/'.$pageId;
        }

        throw new \InvalidArgumentException('Enter a Messenger username, page ID, or m.me URL.');
    }

    public function rules(): array
    {
        return [
            'input.username' => ['nullable', 'string', 'max:100'],
            'input.page_id' => ['nullable', 'string', 'max:64'],
            'input.url' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.username' => 'Messenger username',
            'input.page_id' => 'page ID',
            'input.url' => 'm.me URL',
        ];
    }
}
