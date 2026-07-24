<?php

namespace App\Models;

use App\Enums\Qr\WorkspaceRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QrWorkspace extends Model
{
    protected $table = 'qr_workspaces';

    protected $fillable = [
        'uuid',
        'owner_id',
        'name',
        'slug',
        'logo_path',
        'brand_primary',
        'brand_secondary',
        'custom_domain',
        'white_label_enabled',
        'support_email',
        'redirect_footer',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'white_label_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (blank($model->slug)) {
                $model->slug = Str::slug($model->name).'-'.Str::lower(Str::random(4));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(QrWorkspaceMember::class, 'workspace_id');
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class, 'workspace_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(QrCampaign::class, 'workspace_id');
    }

    public function brandTemplates(): HasMany
    {
        return $this->hasMany(QrBrandTemplate::class, 'workspace_id');
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset('storage/'.$this->logo_path) : null;
    }

    public function memberRoleFor(?User $user): ?WorkspaceRole
    {
        if (! $user) {
            return null;
        }
        if ((int) $this->owner_id === (int) $user->id) {
            return WorkspaceRole::Owner;
        }
        $member = $this->members()->where('user_id', $user->id)->first();

        return $member?->roleEnum();
    }
}
