<?php

namespace App\Repositories\Qr;

use App\Contracts\Qr\QrCodeRepositoryInterface;
use App\Models\QrCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentQrCodeRepository implements QrCodeRepositoryInterface
{
    public function store(array $attributes): QrCode
    {
        return QrCode::query()->create($attributes);
    }

    public function findByUuid(string $uuid): ?QrCode
    {
        return QrCode::query()->where('uuid', $uuid)->first();
    }

    public function findByShortCode(string $code): ?QrCode
    {
        return QrCode::query()
            ->where('short_code', $code)
            ->where('is_dynamic', true)
            ->first();
    }

    public function recentFor(?int $userId, ?string $sessionId, int $limit = 12): Collection
    {
        return QrCode::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(! $userId && $sessionId, fn ($q) => $q->where('session_id', $sessionId))
            ->when(! $userId && ! $sessionId, fn ($q) => $q->whereRaw('1 = 0'))
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function savedFor(int $userId, int $limit = 50): Collection
    {
        return QrCode::query()
            ->where('user_id', $userId)
            ->where('is_saved', true)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function dynamicForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return QrCode::query()
            ->where('user_id', $userId)
            ->where('is_dynamic', true)
            ->latest()
            ->paginate($perPage);
    }

    public function markSaved(QrCode $qrCode, bool $saved = true): QrCode
    {
        $qrCode->update(['is_saved' => $saved]);

        return $qrCode->refresh();
    }

    public function deleteForOwner(QrCode $qrCode, ?int $userId, ?string $sessionId): bool
    {
        $owns = ($userId && (int) $qrCode->user_id === $userId)
            || ($sessionId && $qrCode->session_id === $sessionId && ! $qrCode->user_id);

        if (! $owns) {
            return false;
        }

        return (bool) $qrCode->delete();
    }
}
