<?php

namespace App\DTOs\VisitingCard;

use App\Enums\VisitingCard\CardTemplate;

final class VisitingCardData
{
    public function __construct(
        public readonly string $fullName,
        public readonly string $jobTitle,
        public readonly string $company,
        public readonly string $phone,
        public readonly string $email,
        public readonly string $website,
        public readonly string $address,
        public readonly string $tagline,
        public readonly CardTemplate $template,
        public readonly string $primaryColor,
        public readonly string $secondaryColor,
        public readonly string $textColor,
        public readonly string $backgroundColor,
        public readonly bool $includeQr,
        public readonly string $qrTarget,
        public readonly ?string $logoPath = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $template = CardTemplate::tryFrom((string) ($data['template'] ?? 'classic')) ?? CardTemplate::Classic;

        return new self(
            fullName: trim((string) ($data['full_name'] ?? '')),
            jobTitle: trim((string) ($data['job_title'] ?? '')),
            company: trim((string) ($data['company'] ?? '')),
            phone: trim((string) ($data['phone'] ?? '')),
            email: trim((string) ($data['email'] ?? '')),
            website: trim((string) ($data['website'] ?? '')),
            address: trim((string) ($data['address'] ?? '')),
            tagline: trim((string) ($data['tagline'] ?? '')),
            template: $template,
            primaryColor: self::hex((string) ($data['primary_color'] ?? '#0B6E4F')),
            secondaryColor: self::hex((string) ($data['secondary_color'] ?? '#F4A259')),
            textColor: self::hex((string) ($data['text_color'] ?? '#1A1A1A')),
            backgroundColor: self::hex((string) ($data['background_color'] ?? '#FFFFFF')),
            includeQr: filter_var($data['include_qr'] ?? false, FILTER_VALIDATE_BOOLEAN),
            qrTarget: (string) ($data['qr_target'] ?? 'website'),
            logoPath: isset($data['logo_path']) && is_string($data['logo_path']) ? $data['logo_path'] : null,
        );
    }

    public static function hex(string $color): string
    {
        $color = trim($color);
        if (preg_match('/^#([A-Fa-f0-9]{6})$/', $color)) {
            return strtoupper($color);
        }
        if (preg_match('/^#([A-Fa-f0-9]{3})$/', $color, $m)) {
            $h = $m[1];

            return strtoupper('#'.$h[0].$h[0].$h[1].$h[1].$h[2].$h[2]);
        }

        return '#0B6E4F';
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    public static function rgb(string $hex): array
    {
        $hex = ltrim(self::hex($hex), '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    public function displayName(): string
    {
        return $this->fullName !== '' ? $this->fullName : 'Your Name';
    }

    public function qrPayload(): string
    {
        return match ($this->qrTarget) {
            'vcard' => $this->toVcard(),
            'email' => $this->email !== '' ? 'mailto:'.$this->email : ($this->website !== '' ? $this->normalizeUrl($this->website) : 'https://calchubnepal.com'),
            'phone' => $this->phone !== '' ? 'tel:'.preg_replace('/[^\d+]/', '', $this->phone) : $this->normalizeUrl($this->website ?: 'https://calchubnepal.com'),
            default => $this->normalizeUrl($this->website !== '' ? $this->website : 'https://calchubnepal.com'),
        };
    }

    public function toVcard(): string
    {
        $parts = preg_split('/\s+/', $this->fullName, 2) ?: [];
        $first = $parts[0] ?? '';
        $last = $parts[1] ?? '';

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'N:'.$last.';'.$first.';;;',
            'FN:'.($this->fullName !== '' ? $this->fullName : 'Contact'),
        ];
        if ($this->company !== '') {
            $lines[] = 'ORG:'.$this->company;
        }
        if ($this->jobTitle !== '') {
            $lines[] = 'TITLE:'.$this->jobTitle;
        }
        if ($this->phone !== '') {
            $lines[] = 'TEL;TYPE=CELL:'.preg_replace('/[^\d+]/', '', $this->phone);
        }
        if ($this->email !== '') {
            $lines[] = 'EMAIL:'.$this->email;
        }
        if ($this->website !== '') {
            $lines[] = 'URL:'.$this->normalizeUrl($this->website);
        }
        if ($this->address !== '') {
            $lines[] = 'ADR:;;'.$this->address.';;;;';
        }
        $lines[] = 'END:VCARD';

        return implode("\n", $lines);
    }

    protected function normalizeUrl(string $url): string
    {
        if ($url === '') {
            return 'https://calchubnepal.com';
        }
        if (! preg_match('#^https?://#i', $url)) {
            return 'https://'.$url;
        }

        return $url;
    }
}
