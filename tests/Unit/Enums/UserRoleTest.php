<?php

use App\Enums\UserRole;

it('has correct values for all roles', function () {
    expect(UserRole::ADMIN_DINSOS->value)->toBe('admin_dinsos')
        ->and(UserRole::OPERATOR_BIDANG->value)->toBe('operator_bidang')
        ->and(UserRole::VERIFIKATOR->value)->toBe('verifikator')
        ->and(UserRole::OPERATOR_DESA->value)->toBe('operator_desa');
});

it('has correct labels for all roles', function () {
    expect(UserRole::ADMIN_DINSOS->label())->toBe('Admin Dinsos')
        ->and(UserRole::OPERATOR_BIDANG->label())->toBe('Operator Bidang Dinsos')
        ->and(UserRole::VERIFIKATOR->label())->toBe('Verifikator Dinsos')
        ->and(UserRole::OPERATOR_DESA->label())->toBe('Operator Desa');
});

it('admin dinsos has all permissions', function () {
    $permissions = UserRole::ADMIN_DINSOS->permissions();

    expect($permissions)
        ->toContain('user.manage')
        ->toContain('user.reset-password')
        ->toContain('audit-log.view')
        ->toContain('data.create')
        ->toContain('data.edit')
        ->toContain('data.delete')
        ->toContain('data.view')
        ->toContain('data.verify');
});

it('operator bidang cannot delete or verify data', function () {
    $permissions = UserRole::OPERATOR_BIDANG->permissions();

    expect($permissions)
        ->not->toContain('data.delete')
        ->not->toContain('data.verify')
        ->not->toContain('user.manage');
});

it('verifikator can only view and verify', function () {
    $permissions = UserRole::VERIFIKATOR->permissions();

    expect($permissions)
        ->toContain('data.view')
        ->toContain('data.verify')
        ->not->toContain('data.create')
        ->not->toContain('data.edit')
        ->not->toContain('data.delete');
});

it('operator desa cannot delete or manage users', function () {
    $permissions = UserRole::OPERATOR_DESA->permissions();

    expect($permissions)
        ->not->toContain('data.delete')
        ->not->toContain('user.manage')
        ->not->toContain('data.verify');
});
