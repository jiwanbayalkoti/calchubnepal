<?php

namespace App\DTOs\Qr;

use App\Enums\Qr\QrOutputFormat;
use App\Enums\Qr\QrType;

final class QrGenerateResult
{
    public function __construct(
        public readonly string $payload,
        public readonly string $binary,
        public readonly QrOutputFormat $format,
        public readonly QrType $type,
        public readonly int $size,
        public readonly string $mimeType,
    ) {
    }

    public function dataUri(): string
    {
        return 'data:'.$this->mimeType.';base64,'.base64_encode($this->binary);
    }

    /**
     * @return array<string, mixed>
     */
    public function toPreviewArray(): array
    {
        return [
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'payload' => $this->payload,
            'format' => $this->format->value,
            'size' => $this->size,
            'mime_type' => $this->mimeType,
            'image' => $this->dataUri(),
            'characters' => function_exists('mb_strlen') ? mb_strlen($this->payload) : strlen($this->payload),
        ];
    }
}
