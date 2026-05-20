<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\DtsenRekap;
use App\Models\User;

class DtsenRekapPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::OPERATOR_DESA->value,
            UserRole::VERIFIKATOR->value,
        ]);
    }

    public function view(User $user, DtsenRekap $rekap): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value);
    }

    public function update(User $user, DtsenRekap $rekap): bool
    {
        return false; // Immutable — tidak bisa diedit
    }

    public function delete(User $user, DtsenRekap $rekap): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value);
    }

    public function forceDelete(User $user, DtsenRekap $rekap): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value);
    }

    public function restore(User $user, DtsenRekap $rekap): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value);
    }

    public function download(User $user, DtsenRekap $rekap): bool
    {
        return $this->viewAny($user);
    }
}
