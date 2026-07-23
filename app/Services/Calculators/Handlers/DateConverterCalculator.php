<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use App\Services\Date\NepaliCalendar;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Convert Gregorian (AD) dates to Bikram Sambat (BS) and vice versa.
 */
class DateConverterCalculator extends AbstractCalculatorHandler
{
    private const MONTHS_EN = [
        1 => 'Baishakh',
        2 => 'Jestha',
        3 => 'Ashadh',
        4 => 'Shrawan',
        5 => 'Bhadra',
        6 => 'Ashwin',
        7 => 'Kartik',
        8 => 'Mangsir',
        9 => 'Poush',
        10 => 'Magh',
        11 => 'Falgun',
        12 => 'Chaitra',
    ];

    private const MONTHS_NP = [
        1 => 'बैशाख',
        2 => 'जेष्ठ',
        3 => 'असार',
        4 => 'श्रावण',
        5 => 'भदौ',
        6 => 'असोज',
        7 => 'कार्तिक',
        8 => 'मंसिर',
        9 => 'पुष',
        10 => 'माघ',
        11 => 'फाल्गुन',
        12 => 'चैत',
    ];

    private const WEEKDAYS = [
        1 => 'Sunday',
        2 => 'Monday',
        3 => 'Tuesday',
        4 => 'Wednesday',
        5 => 'Thursday',
        6 => 'Friday',
        7 => 'Saturday',
    ];

    public function __construct(protected NepaliCalendar $calendar)
    {
    }

    public function key(): string
    {
        return 'date_converter_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('direction', 'Conversion', 'select', [
                'options' => [
                    'ad_to_bs' => 'AD → BS (English to Nepali)',
                    'bs_to_ad' => 'BS → AD (Nepali to English)',
                ],
                'default' => 'ad_to_bs',
            ]),
            $this->field('ad_date', 'AD Date (Gregorian)', 'date', [
                'required' => false,
                'help' => 'Required for AD → BS. Supported: 1944-01-01 to 2033-04-13.',
            ]),
            $this->field('bs_date', 'BS Date (Bikram Sambat)', 'bs_date', [
                'required' => false,
                'help' => 'Required for BS → AD. Pick from the Nepali calendar.',
                'default' => '2083-04-07',
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $direction = $this->toString($inputs, 'direction', 'ad_to_bs');

        if ($direction === 'bs_to_ad') {
            return $this->convertBsToAd($inputs);
        }

        return $this->convertAdToBs($inputs);
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @return array{0: int, 1: int, 2: int}
     */
    protected function resolveBsParts(array $inputs): array
    {
        $bsDate = trim(str_replace('/', '-', $this->toString($inputs, 'bs_date')));

        if ($bsDate !== '' && preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $bsDate, $m)) {
            return [(int) $m[1], (int) $m[2], (int) $m[3]];
        }

        return [
            $this->toInt($inputs, 'bs_year'),
            $this->toInt($inputs, 'bs_month'),
            $this->toInt($inputs, 'bs_day'),
        ];
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @return array{results: array<string, mixed>, breakdown: array<string, mixed>, units: array<string, string>}
     */
    protected function convertAdToBs(array $inputs): array
    {
        $adRaw = $this->toString($inputs, 'ad_date');

        if ($adRaw === '') {
            throw new InvalidArgumentException('Please enter an AD (Gregorian) date for AD → BS conversion.');
        }

        $ad = Carbon::parse($adRaw)->startOfDay();
        $converted = $this->calendar->convertEnglishToNepali($ad->year, $ad->month, $ad->day);

        if (! is_array($converted) || empty($converted['year'])) {
            throw new InvalidArgumentException('AD date is out of supported range (1944–2033).');
        }

        $bsYear = (int) $converted['year'];
        $bsMonth = (int) $converted['month'];
        $bsDay = (int) $converted['day'];
        $weekday = (int) ($converted['weekday'] ?? 0);

        $monthEn = self::MONTHS_EN[$bsMonth] ?? (string) $bsMonth;
        $monthNp = self::MONTHS_NP[$bsMonth] ?? $monthEn;
        $bsFormatted = sprintf('%04d-%02d-%02d', $bsYear, $bsMonth, $bsDay);
        $bsReadable = sprintf('%d %s %d', $bsDay, $monthEn, $bsYear);

        return [
            'results' => [
                'bs_date' => $bsFormatted,
                'bs_readable' => $bsReadable,
                'bs_month_name' => $monthEn,
                'weekday' => self::WEEKDAYS[$weekday] ?? '',
            ],
            'breakdown' => [
                'direction' => 'AD → BS',
                'ad_date' => $ad->toDateString(),
                'ad_readable' => $ad->format('F j, Y'),
                'bs_year' => $bsYear,
                'bs_month' => $bsMonth,
                'bs_day' => $bsDay,
                'bs_month_nepali' => $monthNp,
                'supported_range' => 'AD 1944–2033 / BS 2000–2089',
            ],
            'units' => [
                'bs_date' => 'BS',
                'bs_readable' => '',
                'bs_month_name' => '',
                'weekday' => '',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @return array{results: array<string, mixed>, breakdown: array<string, mixed>, units: array<string, string>}
     */
    protected function convertBsToAd(array $inputs): array
    {
        [$year, $month, $day] = $this->resolveBsParts($inputs);

        if ($year < 2000 || $month < 1 || $day < 1) {
            throw new InvalidArgumentException('Please pick a BS date from the Nepali calendar for BS → AD conversion.');
        }

        $converted = $this->calendar->convertNepaliToEnglish($year, $month, $day);

        if (! is_array($converted) || empty($converted['year'])) {
            throw new InvalidArgumentException('BS date is invalid or out of supported range (BS 2000–2089).');
        }

        $adYear = (int) $converted['year'];
        $adMonth = (int) $converted['month'];
        $adDay = (int) $converted['day'];
        $weekday = (int) ($converted['weekday'] ?? 0);

        $ad = Carbon::create($adYear, $adMonth, $adDay)->startOfDay();
        $monthEn = self::MONTHS_EN[$month] ?? (string) $month;
        $monthNp = self::MONTHS_NP[$month] ?? $monthEn;
        $bsFormatted = sprintf('%04d-%02d-%02d', $year, $month, $day);

        return [
            'results' => [
                'ad_date' => $ad->toDateString(),
                'ad_readable' => $ad->format('F j, Y'),
                'weekday' => self::WEEKDAYS[$weekday] ?? $ad->format('l'),
            ],
            'breakdown' => [
                'direction' => 'BS → AD',
                'bs_date' => $bsFormatted,
                'bs_readable' => sprintf('%d %s %d', $day, $monthEn, $year),
                'bs_month_name' => $monthEn,
                'bs_month_nepali' => $monthNp,
                'ad_year' => $adYear,
                'ad_month' => $adMonth,
                'ad_day' => $adDay,
                'supported_range' => 'AD 1944–2033 / BS 2000–2089',
            ],
            'units' => [
                'ad_date' => 'AD',
                'ad_readable' => '',
                'weekday' => '',
            ],
        ];
    }
}
