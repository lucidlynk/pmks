<?php

namespace App\Filament\Traits;

use App\Enums\UserRole;

trait HasRoleAccess
{
    // Override di setiap Resource sesuai kebutuhan
    public static function canAccess(): bool
    {
        return auth()->check();
    }

    protected static function allowedRoles(): array
    {
        return []; // diisi di masing-masing Resource
    }

    protected static function userHasRole(array $roles): bool
    {
        return auth()->user()?->hasAnyRole($roles) ?? false;
    }
}
