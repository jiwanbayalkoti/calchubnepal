<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'google_id',
    'password',
    'role_id',
    'phone',
    'avatar',
    'locale',
    'is_active',
    'is_premium',
    'premium_expires_at',
    'email_verified_at',
    'last_login_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_premium' => 'boolean',
            'premium_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * The user's single "primary" role, set via `users.role_id`.
     */
    public function primaryRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * All roles assigned to the user through the `user_role` pivot table.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function hasRole(string $slug): bool
    {
        if ($this->primaryRole?->slug === $slug) {
            return true;
        }

        return $this->roles->contains('slug', $slug);
    }

    public function hasPermission(string $slug): bool
    {
        $roleIds = $this->roles->pluck('id');

        if ($this->role_id) {
            $roleIds->push($this->role_id);
        }

        if ($roleIds->isEmpty()) {
            return false;
        }

        return Role::whereIn('id', $roleIds->unique())
            ->whereHas('permissions', fn ($query) => $query->where('slug', $slug))
            ->exists();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(self::class, 'updated_by');
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->latestOfMany();
    }

    public function isSubscribed(): bool
    {
        $active = $this->activeSubscription;

        return $active !== null && $active->isActive();
    }

    public function savedCalculations(): HasMany
    {
        return $this->hasMany(SavedCalculation::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(CalculatorFavorite::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(CalculationHistory::class);
    }

    public function advertiser(): HasOne
    {
        return $this->hasOne(Advertiser::class);
    }

    public function isAdvertiser(): bool
    {
        return $this->hasRole('advertiser') || $this->advertiser()->exists();
    }

    public function apiKeysActive(): HasMany
    {
        return $this->apiKeys()->where('is_active', true);
    }

    public function isPremiumActive(): bool
    {
        return $this->is_premium
            && (! $this->premium_expires_at || $this->premium_expires_at->isFuture());
    }

    public function canAccessAdmin(): bool
    {
        return $this->hasRole('super-admin')
            || $this->hasRole('admin')
            || $this->hasPermission('admin.dashboard.view');
    }

    public function homePath(): string
    {
        if ($this->canAccessAdmin()) {
            return route('admin.dashboard', absolute: false);
        }

        if ($this->isAdvertiser()) {
            return route('advertiser.dashboard', absolute: false);
        }

        return route('account.dashboard', absolute: false);
    }

    /**
     * Free-plan saved-calculation limit (null = unlimited for premium).
     */
    public function savedCalculationsLimit(): ?int
    {
        if ($this->isPremiumActive() || $this->isSubscribed()) {
            return null;
        }

        return (int) config('calculator_hub.premium.free_plan.saved_calculations_limit', 5);
    }

    public function canSaveMoreCalculations(): bool
    {
        $limit = $this->savedCalculationsLimit();

        if ($limit === null) {
            return true;
        }

        return $this->savedCalculations()->count() < $limit;
    }
}
