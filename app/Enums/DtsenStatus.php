<?php

namespace App\Enums;

enum DtsenStatus: string
{
    case DRAFT      = 'draft';
    case SUBMITTED  = 'submitted';
    case ON_PROCESS = 'on_process';
    case READY      = 'ready';
    case CANCELLED  = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::DRAFT      => 'Draft',
            self::SUBMITTED  => 'Diajukan',
            self::ON_PROCESS => 'Sedang Diproses',
            self::READY      => 'Siap Diunduh',
            self::CANCELLED  => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT      => 'gray',
            self::SUBMITTED  => 'info',
            self::ON_PROCESS => 'warning',
            self::READY      => 'success',
            self::CANCELLED  => 'danger',
        };
    }

    public function canEdit(): bool
    {
        return $this === self::DRAFT;
    }

    public function canSubmit(): bool
    {
        return $this === self::DRAFT;
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::DRAFT, self::SUBMITTED]);
    }

    public function canProcess(): bool
    {
        return $this === self::SUBMITTED;
    }

    public function canUploadPdf(): bool
    {
        return $this === self::ON_PROCESS;
    }

    public function canDownloadPdf(): bool
    {
        return $this === self::READY;
    }

    public function canDelete(): bool
    {
        return in_array($this, [self::DRAFT, self::CANCELLED]);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->toArray();
    }
}
