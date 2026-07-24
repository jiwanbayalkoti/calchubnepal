<?php

namespace App\Repositories\Qr;

use App\Contracts\Qr\QrCodeRepositoryInterface;
use App\Models\QrCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Legacy Phase-1 null repository. Not bound — Eloquent is used.
 */
class NullQrCodeRepository implements QrCodeRepositoryInterface
{
    public function store(array $attributes): QrCode
    {
        throw new RuntimeException('QR persistence is not enabled.');
    }

    public function findByUuid(string $uuid): ?QrCode
    {
        return null;
    }

    public function findByShortCode(string $code): ?QrCode
    {
        return null;
    }

    public function recentFor(?int $userId, ?string $sessionId, int $limit = 12): Collection
    {
        return collect();
    }

    public function savedFor(int $userId, int $limit = 50): Collection
    {
        return collect();
    }

    public function dynamicForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return new Paginator([], 0, $perPage);
    }

    public function markSaved(QrCode $qrCode, bool $saved = true): QrCode
    {
        throw new RuntimeException('QR persistence is not enabled.');
    }

    public function deleteForOwner(QrCode $qrCode, ?int $userId, ?string $sessionId): bool
    {
        return false;
    }
}
