<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\BansosImport;
use App\Models\User;

class BansosImportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::VERIFIKATOR->value,
            UserRole::OPERATOR_DESA->value,
            UserRole::STAF_DINSOS->value,
        ]);
    }

    public function view(User $user, BansosImport $import): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::VERIFIKATOR->value,
            UserRole::OPERATOR_DESA->value,
            UserRole::STAF_DINSOS->value,
        ]);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value);
    }

    public function delete(User $user, BansosImport $import): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value)
            && $import->isFinished();
    }

    public function download(User $user, BansosImport $import): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::STAF_DINSOS->value,
            UserRole::OPERATOR_DESA->value,
        ]) && $import->isDone();
    }
}
