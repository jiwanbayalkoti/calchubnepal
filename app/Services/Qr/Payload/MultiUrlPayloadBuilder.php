<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class MultiUrlPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::MultiUrl;
    }

    public function build(array $input): string
    {
        $title = $this->string($input, 'title', 'Links');
        $raw = $this->requireNonEmpty($this->string($input, 'urls'), 'URLs');
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $links = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (! preg_match('#^https?://#i', $line)) {
                $line = 'https://'.$line;
            }
            $links[] = $line;
        }

        if ($links === []) {
            throw new \InvalidArgumentException('Enter at least one URL (one per line).');
        }

        $out = [$title, str_repeat('-', min(24, max(8, strlen($title))))];
        foreach ($links as $i => $link) {
            $out[] = ($i + 1).'. '.$link;
        }

        return implode("\n", $out);
    }

    public function rules(): array
    {
        return [
            'input.title' => ['nullable', 'string', 'max:80'],
            'input.urls' => ['required', 'string', 'max:4000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.title' => 'list title',
            'input.urls' => 'URLs',
        ];
    }
}
