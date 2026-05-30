<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\KisPbiApbdImport;
use App\Models\User;

class KisPbiApbdImportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::VERIFIKATOR->value,
        ]);
    }

    public function view(User $user, KisPbiApbdImport $import): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::VERIFIKATOR->value,
        ]);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
        ]);
    }

    public function delete(User $user, KisPbiApbdImport $import): bool
    {
        // Hanya bisa hapus kalau status done atau failed
        return $user->hasRole(UserRole::ADMIN_DINSOS->value)
            && $import->isFinished();
    }

    public function download(User $user, KisPbiApbdImport $import): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value)
            && $import->isDone();
    }
}
