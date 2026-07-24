<?php

namespace App\Enums\Qr;

enum WorkspaceRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
            self::Member => 'Member',
            self::Viewer => 'Viewer',
        };
    }

    public function canManage(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }

    public function canEdit(): bool
    {
        return in_array($this, [self::Owner, self::Admin, self::Member], true);
    }
}
