<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\PmksSubmission;
use App\Models\User;

class PmksSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PmksSubmission $submission): bool
    {
        if ($user->isAdminDinsos()) return true;
        if ($user->hasRole(UserRole::VERIFIKATOR->value)) return true;
        if ($user->hasRole(UserRole::OPERATOR_BIDANG->value)) return true;

        if ($user->isOperatorDesa()) {
            return $user->village_id === $submission->village_id;
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

    public function update(User $user, PmksSubmission $submission): bool
    {
        if (!$submission->batch?->canBeEdited()) return false;

        if ($user->isAdminDinsos()) return true;
        if ($user->hasRole(UserRole::OPERATOR_BIDANG->value)) return true;

        if ($user->isOperatorDesa()) {
            return $user->village_id === $submission->village_id;
        }

        return false;
    }

    public function delete(User $user, PmksSubmission $submission): bool
    {
        if (!$submission->batch?->canBeEdited()) return false;
        return $user->isAdminDinsos() ||
               ($user->isOperatorDesa() && $user->village_id === $submission->village_id);
    }
}
