<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class SocialPayloadBuilder extends AbstractQrPayloadBuilder
{
    private const NETWORKS = [
        'facebook' => 'https://facebook.com/',
        'instagram' => 'https://instagram.com/',
        'twitter' => 'https://x.com/',
        'linkedin' => 'https://linkedin.com/in/',
        'youtube' => 'https://youtube.com/',
        'tiktok' => 'https://tiktok.com/@',
        'github' => 'https://github.com/',
    ];

    public function type(): QrType
    {
        return QrType::Social;
    }

    public function build(array $input): string
    {
        $network = strtolower($this->string($input, 'network', 'instagram'));
        $username = ltrim($this->string($input, 'username'), '@/');
        $customUrl = $this->string($input, 'url');

        if ($customUrl !== '') {
            if (! preg_match('#^https?://#i', $customUrl)) {
                $customUrl = 'https://'.$customUrl;
            }

            return $customUrl;
        }

        if ($username === '' || ! isset(self::NETWORKS[$network])) {
            throw new \InvalidArgumentException('Select a network and enter a username, or paste a full profile URL.');
        }

        return self::NETWORKS[$network].rawurlencode($username);
    }

    public function rules(): array
    {
        return [
            'input.network' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::NETWORKS))],
            'input.username' => ['nullable', 'string', 'max:100'],
            'input.url' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.network' => 'social network',
            'input.username' => 'username',
            'input.url' => 'profile URL',
        ];
    }
}
