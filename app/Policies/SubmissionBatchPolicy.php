<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\SubmissionBatch;
use App\Models\User;

class SubmissionBatchPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SubmissionBatch $batch): bool
    {
        if ($user->isAdminDinsos()) return true;
        if ($user->hasRole(UserRole::VERIFIKATOR->value)) return true;
        if ($user->hasRole(UserRole::OPERATOR_BIDANG->value)) return true;

        // Operator Desa hanya bisa lihat batch desanya
        if ($user->isOperatorDesa()) {
            return $user->village_id === $batch->village_id;
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

    public function update(User $user, SubmissionBatch $batch): bool
    {
        if (!$batch->canBeEdited()) return false;

        if ($user->isAdminDinsos()) return true;
        if ($user->hasRole(UserRole::OPERATOR_BIDANG->value)) return true;

        if ($user->isOperatorDesa()) {
            return $user->village_id === $batch->village_id;
        }

        return false;
    }

    public function delete(User $user, SubmissionBatch $batch): bool
    {
        if (!$batch->isDraft()) return false;
        return $user->isAdminDinsos();
    }
}
