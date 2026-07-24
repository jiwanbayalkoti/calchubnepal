<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class CouponPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Coupon;
    }

    public function build(array $input): string
    {
        $code = $this->requireNonEmpty($this->string($input, 'code'), 'Promo code');
        $title = $this->string($input, 'title', 'Promo code');
        $url = $this->string($input, 'url');
        $expires = $this->string($input, 'expires');
        $terms = $this->string($input, 'terms');

        if ($url !== '') {
            if (! preg_match('#^https?://#i', $url)) {
                $url = 'https://'.$url;
            }
            $sep = str_contains($url, '?') ? '&' : '?';

            return $url.$sep.'promo='.rawurlencode($code);
        }

        $lines = [
            strtoupper($title),
            'Code: '.$code,
        ];
        if ($expires !== '') {
            $lines[] = 'Expires: '.$expires;
        }
        if ($terms !== '') {
            $lines[] = $terms;
        }

        return implode("\n", $lines);
    }

    public function rules(): array
    {
        return [
            'input.code' => ['required', 'string', 'max:64'],
            'input.title' => ['nullable', 'string', 'max:80'],
            'input.url' => ['nullable', 'string', 'max:500'],
            'input.expires' => ['nullable', 'string', 'max:40'],
            'input.terms' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.code' => 'promo code',
            'input.title' => 'title',
            'input.url' => 'redeem URL',
            'input.expires' => 'expiry',
            'input.terms' => 'terms',
        ];
    }
}
