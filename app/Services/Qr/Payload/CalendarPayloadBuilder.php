<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;
use Carbon\Carbon;

class CalendarPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Calendar;
    }

    public function build(array $input): string
    {
        $title = $this->requireNonEmpty($this->string($input, 'title'), 'Event title');
        $start = $this->requireNonEmpty($this->string($input, 'start'), 'Start date/time');
        $end = $this->string($input, 'end');
        $details = $this->string($input, 'details');
        $location = $this->string($input, 'location');

        try {
            $startAt = Carbon::parse($start)->utc();
            $endAt = $end !== '' ? Carbon::parse($end)->utc() : (clone $startAt)->addHour();
        } catch (\Throwable) {
            throw new \InvalidArgumentException('Enter a valid start (and optional end) date/time.');
        }

        $params = [
            'action' => 'TEMPLATE',
            'text' => $title,
            'dates' => $startAt->format('Ymd\THis\Z').'/'.$endAt->format('Ymd\THis\Z'),
        ];
        if ($details !== '') {
            $params['details'] = $details;
        }
        if ($location !== '') {
            $params['location'] = $location;
        }

        return 'https://calendar.google.com/calendar/render?'.http_build_query($params);
    }

    public function rules(): array
    {
        return [
            'input.title' => ['required', 'string', 'max:200'],
            'input.start' => ['required', 'string', 'max:40'],
            'input.end' => ['nullable', 'string', 'max:40'],
            'input.details' => ['nullable', 'string', 'max:500'],
            'input.location' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.title' => 'event title',
            'input.start' => 'start',
            'input.end' => 'end',
            'input.details' => 'details',
            'input.location' => 'location',
        ];
    }
}
