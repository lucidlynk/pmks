<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\DtsenRequest;
use App\Models\User;

class DtsenRequestPolicy
{
    // -------------------------------------------------------------------------
    // Siapa yang bisa lihat list
    // -------------------------------------------------------------------------

    public function viewAny(User $user): bool
    {
        return true; // semua role bisa lihat, scope diatur di Resource
    }

    // -------------------------------------------------------------------------
    // Siapa yang bisa lihat detail
    // -------------------------------------------------------------------------

    public function view(User $user, DtsenRequest $request): bool
    {
        if ($user->hasRole(UserRole::OPERATOR_DESA->value)) {
            return $user->village_id === $request->village_id;
        }

        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::VERIFIKATOR->value,
            UserRole::OPERATOR_BIDANG->value,
        ]);
    }

    // -------------------------------------------------------------------------
    // Buat permohonan baru — hanya Operator Desa
    // -------------------------------------------------------------------------

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::OPERATOR_DESA->value);
    }

    // -------------------------------------------------------------------------
    // Edit — hanya Operator Desa, hanya saat DRAFT
    // -------------------------------------------------------------------------

    public function update(User $user, DtsenRequest $request): bool
    {
        return $user->hasRole(UserRole::OPERATOR_DESA->value)
            && $user->village_id === $request->village_id
            && $request->status->canEdit();
    }

    // -------------------------------------------------------------------------
    // Hapus — Admin atau Operator Desa pemilik, hanya status yang boleh hapus
    // -------------------------------------------------------------------------

    public function delete(User $user, DtsenRequest $request): bool
    {
        if (! $request->status->canDelete()) {
            return false;
        }

        if ($user->hasRole(UserRole::ADMIN_DINSOS->value)) {
            return true;
        }

        return $user->hasRole(UserRole::OPERATOR_DESA->value)
            && $user->village_id === $request->village_id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value);
    }

    // -------------------------------------------------------------------------
    // Submit — Operator Desa pemilik, status DRAFT
    // -------------------------------------------------------------------------

    public function submit(User $user, DtsenRequest $request): bool
    {
        return $user->hasRole(UserRole::OPERATOR_DESA->value)
            && $user->village_id === $request->village_id
            && $request->status->canSubmit();
    }

    // -------------------------------------------------------------------------
    // Batalkan — Operator Desa pemilik, status DRAFT/SUBMITTED
    // -------------------------------------------------------------------------

    public function cancel(User $user, DtsenRequest $request): bool
    {
        return $user->hasRole(UserRole::OPERATOR_DESA->value)
            && $user->village_id === $request->village_id
            && $request->status->canCancel();
    }

    // -------------------------------------------------------------------------
    // Proses (SUBMITTED -> ON_PROCESS) — Admin Dinsos & Verifikator
    // -------------------------------------------------------------------------

    public function process(User $user, DtsenRequest $request): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::VERIFIKATOR->value,
        ])
            && $request->status->canProcess();
    }

    // -------------------------------------------------------------------------
    // Upload PDF — Admin Dinsos & Verifikator, status ON_PROCESS
    // -------------------------------------------------------------------------

    public function uploadPdf(User $user, DtsenRequest $request): bool
    {
        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::VERIFIKATOR->value,
        ])
            && $request->status->canUploadPdf();
    }

    // -------------------------------------------------------------------------
    // Download PDF — Operator Desa pemilik desa, status READY
    // -------------------------------------------------------------------------

    public function downloadPdf(User $user, DtsenRequest $request): bool
    {
        if (! $request->status->canDownloadPdf()) {
            return false;
        }

        if ($user->hasRole(UserRole::OPERATOR_DESA->value)) {
            return $user->village_id === $request->village_id;
        }

        return $user->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::VERIFIKATOR->value,
        ]);
    }
}
