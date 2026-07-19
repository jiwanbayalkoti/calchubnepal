<?php

namespace App\Services\Activity;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Records user/system activity for auditing (logins, CRUD, AI requests,
 * API calls, payment events, etc. per the project's audit-log requirement).
 */
class ActivityLogService
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function log(string $action, string $module, ?Model $subject = null, array $properties = [], ?int $userId = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'module' => $module,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => (string) Request::header('User-Agent'),
        ]);
    }

    /**
     * @return Collection<int, ActivityLog>
     */
    public function forUser(int $userId, int $limit = 50): Collection
    {
        return ActivityLog::query()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, ActivityLog>
     */
    public function forModule(string $module, int $limit = 50): Collection
    {
        return ActivityLog::query()
            ->where('module', $module)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, ActivityLog>
     */
    public function recent(int $limit = 50): Collection
    {
        return ActivityLog::query()->latest('created_at')->limit($limit)->get();
    }
}
