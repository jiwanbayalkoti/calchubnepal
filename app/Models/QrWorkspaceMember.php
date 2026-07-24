<?php

namespace App\Models;

use App\Enums\Qr\WorkspaceRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrWorkspaceMember extends Model
{
    protected $table = 'qr_workspace_members';

    protected $fillable = [
        'workspace_id',
        'user_id',
        'role',
        'invited_email',
        'invite_token',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(QrWorkspace::class, 'workspace_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function roleEnum(): WorkspaceRole
    {
        return WorkspaceRole::tryFrom((string) $this->role) ?? WorkspaceRole::Member;
    }
}
