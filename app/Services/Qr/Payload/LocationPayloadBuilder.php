<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class LocationPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Location;
    }

    public function build(array $input): string
    {
        $lat = $this->requireNonEmpty($this->string($input, 'lat'), 'Latitude');
        $lng = $this->requireNonEmpty($this->string($input, 'lng'), 'Longitude');
        $altitude = $this->string($input, 'altitude');

        $geo = 'geo:'.$lat.','.$lng;
        if ($altitude !== '') {
            $geo .= ','.$altitude;
        }

        return $geo;
    }

    public function rules(): array
    {
        return [
            'input.lat' => ['required', 'numeric', 'between:-90,90'],
            'input.lng' => ['required', 'numeric', 'between:-180,180'],
            'input.altitude' => ['nullable', 'numeric'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.lat' => 'latitude',
            'input.lng' => 'longitude',
            'input.altitude' => 'altitude',
        ];
    }
}
