<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\QrCode
 */
class QrCodeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'short_code' => $this->short_code,
            'short_url' => $this->shortUrl(),
            'title' => $this->title,
            'type' => $this->type,
            'destination_url' => $this->destination_url,
            'is_dynamic' => (bool) $this->is_dynamic,
            'status' => $this->status?->value ?? $this->status,
            'password_protected' => $this->isPasswordProtected(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'scan_count' => (int) $this->scan_count,
            'last_scanned_at' => $this->last_scanned_at?->toIso8601String(),
            'preview_url' => $this->previewUrl(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
