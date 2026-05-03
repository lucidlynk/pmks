<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN_DINSOS       = 'admin_dinsos';
    case OPERATOR_BIDANG    = 'operator_bidang';
    case VERIFIKATOR        = 'verifikator';
    case OPERATOR_DESA      = 'operator_desa';

    public function label(): string
    {
        return match($this) {
            self::ADMIN_DINSOS    => 'Admin Dinsos',
            self::OPERATOR_BIDANG => 'Operator Bidang Dinsos',
            self::VERIFIKATOR     => 'Verifikator Dinsos',
            self::OPERATOR_DESA   => 'Operator Desa',
        };
    }

    public function permissions(): array
    {
        return match($this) {
            self::ADMIN_DINSOS => [
                'user.manage',
                'user.reset-password',
                'audit-log.view',
                'data.create',
                'data.edit',
                'data.delete',
                'data.view',
                'data.verify',
            ],
            self::OPERATOR_BIDANG => [
                'data.create',
                'data.edit',
                'data.view',
            ],
            self::VERIFIKATOR => [
                'data.view',
                'data.verify',
            ],
            self::OPERATOR_DESA => [
                'data.create',
                'data.edit',
                'data.view',
            ],
        };
    }
}
