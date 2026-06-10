<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\PsksImport;
use App\Models\User;

class PsksImportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::VERIFIKATOR->value,
        ]);
    }

    public function view(User $user, PsksImport $import): bool
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

    public function delete(User $user, PsksImport $import): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value)
            && $import->isFinished();
    }
}
