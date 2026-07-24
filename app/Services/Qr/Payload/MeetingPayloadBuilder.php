<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class MeetingPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Meeting;
    }

    public function build(array $input): string
    {
        $platform = strtolower($this->string($input, 'platform', 'zoom'));
        $url = $this->string($input, 'url');
        $id = $this->string($input, 'meeting_id');
        $pass = $this->string($input, 'password');

        if ($url !== '') {
            if (! preg_match('#^https?://#i', $url)) {
                $url = 'https://'.$url;
            }

            return $url;
        }

        if ($id === '') {
            throw new \InvalidArgumentException('Paste a meeting URL or enter a meeting ID.');
        }

        return match ($platform) {
            'meet' => 'https://meet.google.com/'.$id,
            'teams' => 'https://teams.microsoft.com/l/meetup-join/'.$id,
            default => 'https://zoom.us/j/'.preg_replace('/\D/', '', $id).($pass !== '' ? '?pwd='.rawurlencode($pass) : ''),
        };
    }

    public function rules(): array
    {
        return [
            'input.platform' => ['nullable', 'string', 'in:zoom,meet,teams'],
            'input.url' => ['nullable', 'string', 'max:1000'],
            'input.meeting_id' => ['nullable', 'string', 'max:120'],
            'input.password' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.platform' => 'platform',
            'input.url' => 'meeting URL',
            'input.meeting_id' => 'meeting ID',
            'input.password' => 'password',
        ];
    }
}
