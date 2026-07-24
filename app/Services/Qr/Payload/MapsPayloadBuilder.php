<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class MapsPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Maps;
    }

    public function build(array $input): string
    {
        $provider = strtolower($this->string($input, 'provider', 'google'));
        $query = $this->string($input, 'query');
        $lat = $this->string($input, 'lat');
        $lng = $this->string($input, 'lng');

        $place = $query;
        if ($place === '' && $lat !== '' && $lng !== '') {
            $place = $lat.','.$lng;
        }

        if ($place === '') {
            throw new \InvalidArgumentException('Enter a place name/address or latitude and longitude.');
        }

        return match ($provider) {
            'apple' => 'https://maps.apple.com/?q='.rawurlencode($place),
            'waze' => 'https://waze.com/ul?q='.rawurlencode($place).'&navigate=yes',
            'osm' => 'https://www.openstreetmap.org/search?query='.rawurlencode($place),
            'geo' => $this->geoUri($lat, $lng, $query),
            default => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($place),
        };
    }

    protected function geoUri(string $lat, string $lng, string $query): string
    {
        if ($lat !== '' && $lng !== '') {
            return 'geo:'.$lat.','.$lng.($query !== '' ? '?q='.rawurlencode($query) : '');
        }
        if ($query !== '') {
            return 'geo:0,0?q='.rawurlencode($query);
        }

        throw new \InvalidArgumentException('Geo map needs coordinates or a place name.');
    }

    public function rules(): array
    {
        return [
            'input.provider' => ['nullable', 'string', 'in:google,apple,waze,osm,geo'],
            'input.query' => ['nullable', 'string', 'max:500'],
            'input.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'input.lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.provider' => 'map provider',
            'input.query' => 'place or address',
            'input.lat' => 'latitude',
            'input.lng' => 'longitude',
        ];
    }
}
