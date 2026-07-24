<?php

namespace App\Services\Qr;

use App\Enums\Qr\WorkspaceRole;
use App\Models\QrWorkspace;
use App\Models\QrWorkspaceMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WorkspaceService
{
    public function __construct(protected QrEntitlementService $entitlements)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): QrWorkspace
    {
        $this->entitlements->assertEnterprise($user, 'workspaces');

        return DB::transaction(function () use ($user, $data) {
            $workspace = QrWorkspace::query()->create([
                'owner_id' => $user->id,
                'name' => (string) $data['name'],
                'slug' => Str::slug((string) $data['name']).'-'.Str::lower(Str::random(4)),
                'brand_primary' => (string) ($data['brand_primary'] ?? '#0B6E4F'),
                'brand_secondary' => (string) ($data['brand_secondary'] ?? '#F4A259'),
                'support_email' => $data['support_email'] ?? $user->email,
                'white_label_enabled' => (bool) ($data['white_label_enabled'] ?? false) && $this->entitlements->canUseWhiteLabel($user),
                'custom_domain' => $data['custom_domain'] ?? null,
                'redirect_footer' => $data['redirect_footer'] ?? null,
            ]);

            QrWorkspaceMember::query()->create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role' => WorkspaceRole::Owner->value,
                'joined_at' => now(),
            ]);

            return $workspace;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(QrWorkspace $workspace, User $actor, array $data): QrWorkspace
    {
        $this->assertCanManage($workspace, $actor);

        $updates = array_filter([
            'name' => $data['name'] ?? null,
            'brand_primary' => $data['brand_primary'] ?? null,
            'brand_secondary' => $data['brand_secondary'] ?? null,
            'support_email' => $data['support_email'] ?? null,
            'redirect_footer' => $data['redirect_footer'] ?? null,
            'custom_domain' => $data['custom_domain'] ?? null,
        ], static fn ($v) => $v !== null);

        if (array_key_exists('white_label_enabled', $data)) {
            $updates['white_label_enabled'] = (bool) $data['white_label_enabled']
                && $this->entitlements->canUseWhiteLabel($actor);
        }

        $workspace->update($updates);

        return $workspace->refresh();
    }

    public function invite(QrWorkspace $workspace, User $actor, string $email, string $role = 'member'): QrWorkspaceMember
    {
        $this->assertCanManage($workspace, $actor);
        $roleEnum = WorkspaceRole::tryFrom($role) ?? WorkspaceRole::Member;
        if ($roleEnum === WorkspaceRole::Owner) {
            throw new InvalidArgumentException('Cannot invite another owner.');
        }

        $existingUser = User::query()->where('email', $email)->first();

        return QrWorkspaceMember::query()->updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'user_id' => $existingUser?->id,
                'invited_email' => $existingUser ? null : $email,
            ],
            [
                'role' => $roleEnum->value,
                'invite_token' => $existingUser ? null : Str::random(40),
                'joined_at' => $existingUser ? now() : null,
                'user_id' => $existingUser?->id,
                'invited_email' => $existingUser ? null : $email,
            ]
        );
    }

    public function updateMemberRole(QrWorkspace $workspace, User $actor, QrWorkspaceMember $member, string $role): QrWorkspaceMember
    {
        $this->assertCanManage($workspace, $actor);
        if ((int) $member->user_id === (int) $workspace->owner_id) {
            throw new InvalidArgumentException('Owner role cannot be changed.');
        }
        $roleEnum = WorkspaceRole::tryFrom($role) ?? WorkspaceRole::Member;
        $member->update(['role' => $roleEnum->value]);

        return $member->refresh();
    }

    public function removeMember(QrWorkspace $workspace, User $actor, QrWorkspaceMember $member): void
    {
        $this->assertCanManage($workspace, $actor);
        if ((int) $member->user_id === (int) $workspace->owner_id) {
            throw new InvalidArgumentException('Cannot remove the workspace owner.');
        }
        $member->delete();
    }

    public function assertCanManage(QrWorkspace $workspace, User $actor): void
    {
        $role = $workspace->memberRoleFor($actor);
        if (! $role || ! $role->canManage()) {
            throw new InvalidArgumentException('You do not have permission to manage this workspace.');
        }
    }

    public function assertCanEdit(QrWorkspace $workspace, User $actor): void
    {
        $role = $workspace->memberRoleFor($actor);
        if (! $role || ! $role->canEdit()) {
            throw new InvalidArgumentException('You do not have permission to edit in this workspace.');
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, QrWorkspace>
     */
    public function forUser(User $user)
    {
        $owned = QrWorkspace::query()->where('owner_id', $user->id)->pluck('id');
        $memberOf = QrWorkspaceMember::query()->where('user_id', $user->id)->pluck('workspace_id');

        return QrWorkspace::query()
            ->whereIn('id', $owned->merge($memberOf)->unique())
            ->withCount(['members', 'qrCodes'])
            ->latest()
            ->get();
    }
}
