<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\SubmissionBatch;
use App\Models\User;

class SubmissionBatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::VERIFIKATOR->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::OPERATOR_DESA->value,
        ]);
    }

    public function view(User $user, SubmissionBatch $batch): bool
    {
        if ($user->isAdminDinsos()) return true;
        if ($user->hasRole(UserRole::VERIFIKATOR->value)) return true;
        if ($user->hasRole(UserRole::OPERATOR_BIDANG->value)) return true;

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

    public function deleteAny(User $user): bool
    {
        return $user->isAdminDinsos();
    }

    public function delete(User $user, SubmissionBatch $batch): bool
    {
        if (!$user->isAdminDinsos()) return false;

        // Hanya bisa hapus jika batch belum diproses Dinsos
        return in_array($batch->status, [
            \App\Enums\BatchStatus::DRAFT,
            \App\Enums\BatchStatus::REJECTED,
            \App\Enums\BatchStatus::REVISION_REQUESTED,
            \App\Enums\BatchStatus::REVISED,
        ]);
    }
}
