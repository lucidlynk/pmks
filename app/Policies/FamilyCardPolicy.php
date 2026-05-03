<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\FamilyCard;
use App\Models\User;

class FamilyCardPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FamilyCard $familyCard): bool
    {
        if ($user->isAdminDinsos()) return true;
        if ($user->hasRole(UserRole::VERIFIKATOR->value)) return true;
        if ($user->hasRole(UserRole::OPERATOR_BIDANG->value)) return true;

        if ($user->isOperatorDesa()) {
            return $user->village_id === $familyCard->village_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::OPERATOR_DESA->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::ADMIN_DINSOS->value,
        ]);
    }

    public function update(User $user, FamilyCard $familyCard): bool
    {
        if ($user->isAdminDinsos()) return true;
        if ($user->hasRole(UserRole::OPERATOR_BIDANG->value)) return true;

        if ($user->isOperatorDesa()) {
            return $user->village_id === $familyCard->village_id;
        }

        return false;
    }

    public function delete(User $user, FamilyCard $familyCard): bool
    {
        return $user->isAdminDinsos();
    }
}
