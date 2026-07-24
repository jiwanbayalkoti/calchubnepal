<?php

namespace App\Policies;

use App\Models\QrCode;
use App\Models\User;

class QrCodePolicy
{
    public function view(User $user, QrCode $qrCode): bool
    {
        return (int) $qrCode->user_id === (int) $user->id;
    }

    public function update(User $user, QrCode $qrCode): bool
    {
        return $qrCode->is_dynamic && (int) $qrCode->user_id === (int) $user->id;
    }

    public function delete(User $user, QrCode $qrCode): bool
    {
        return (int) $qrCode->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }
}
