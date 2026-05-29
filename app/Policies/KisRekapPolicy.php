<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\KisRekap;
use App\Models\User;

class KisRekapPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // semua role bisa lihat
    }

    public function view(User $user, KisRekap $kisRekap): bool
    {
        return true; // semua role bisa lihat
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
        ]);
    }

    public function update(User $user, KisRekap $kisRekap): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
        ]);
    }

    public function delete(User $user, KisRekap $kisRekap): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value);
    }
}
