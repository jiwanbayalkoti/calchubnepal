<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class ReviewPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Review;
    }

    public function build(array $input): string
    {
        $url = $this->string($input, 'url');
        $placeId = $this->string($input, 'place_id');

        if ($url !== '') {
            if (! preg_match('#^https?://#i', $url)) {
                $url = 'https://'.$url;
            }

            return $url;
        }

        if ($placeId !== '') {
            return 'https://search.google.com/local/writereview?placeid='.rawurlencode($placeId);
        }

        throw new \InvalidArgumentException('Paste a Google review URL or Place ID.');
    }

    public function rules(): array
    {
        return [
            'input.url' => ['nullable', 'string', 'max:1000'],
            'input.place_id' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.url' => 'review URL',
            'input.place_id' => 'Google Place ID',
        ];
    }
}
