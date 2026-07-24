<?php

namespace App\Contracts\Qr;

use App\Models\QrCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface QrCodeRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function store(array $attributes): QrCode;

    public function findByUuid(string $uuid): ?QrCode;

    public function findByShortCode(string $code): ?QrCode;

    /**
     * @return Collection<int, QrCode>
     */
    public function recentFor(?int $userId, ?string $sessionId, int $limit = 12): Collection;

    /**
     * @return Collection<int, QrCode>
     */
    public function savedFor(int $userId, int $limit = 50): Collection;

    /**
     * @return LengthAwarePaginator<int, QrCode>
     */
    public function dynamicForUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function markSaved(QrCode $qrCode, bool $saved = true): QrCode;

    public function deleteForOwner(QrCode $qrCode, ?int $userId, ?string $sessionId): bool;
}
