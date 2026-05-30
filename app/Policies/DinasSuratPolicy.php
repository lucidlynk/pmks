<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\DinasSurat;
use App\Models\User;

class DinasSuratPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // semua role bisa lihat list
    }

    public function view(User $user, DinasSurat $surat): bool
    {
        return true; // semua role bisa lihat detail & download
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value);
    }

    public function update(User $user, DinasSurat $surat): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value);
    }

    public function delete(User $user, DinasSurat $surat): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value);
    }

    public function restore(User $user, DinasSurat $surat): bool
    {
        return $user->hasRole(UserRole::ADMIN_DINSOS->value);
    }

    public function download(User $user, DinasSurat $surat): bool
    {
        return $surat->is_active && $surat->isVisibleTo($user);
    }
}
