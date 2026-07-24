<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;
use Carbon\Carbon;

class EventPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Event;
    }

    public function build(array $input): string
    {
        $title = $this->requireNonEmpty($this->string($input, 'title'), 'Event title');
        $start = $this->requireNonEmpty($this->string($input, 'start'), 'Start date/time');
        $end = $this->string($input, 'end');
        $location = $this->string($input, 'location');
        $description = $this->string($input, 'description');

        $startAt = Carbon::parse($start);
        $endAt = $end !== '' ? Carbon::parse($end) : $startAt->copy()->addHour();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//CalchubNepal//QR Generator//EN',
            'BEGIN:VEVENT',
            'SUMMARY:'.$this->escapeIcal($title),
            'DTSTART:'.$startAt->utc()->format('Ymd\THis\Z'),
            'DTEND:'.$endAt->utc()->format('Ymd\THis\Z'),
        ];

        if ($location !== '') {
            $lines[] = 'LOCATION:'.$this->escapeIcal($location);
        }
        if ($description !== '') {
            $lines[] = 'DESCRIPTION:'.$this->escapeIcal($description);
        }

        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines);
    }

    protected function escapeIcal(string $value): string
    {
        return str_replace(["\r\n", "\n", ',', ';'], ['\\n', '\\n', '\\,', '\\;'], $value);
    }

    public function rules(): array
    {
        return [
            'input.title' => ['required', 'string', 'max:200'],
            'input.start' => ['required', 'date'],
            'input.end' => ['nullable', 'date', 'after_or_equal:input.start'],
            'input.location' => ['nullable', 'string', 'max:255'],
            'input.description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.title' => 'event title',
            'input.start' => 'start date/time',
            'input.end' => 'end date/time',
            'input.location' => 'location',
            'input.description' => 'description',
        ];
    }
}
