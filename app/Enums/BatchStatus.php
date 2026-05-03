<?php

namespace App\Enums;

enum BatchStatus: string
{
    case DRAFT              = 'draft';
    case SUBMITTED          = 'submitted';
    case VERIFIED           = 'verified';
    case APPROVED           = 'approved';
    case REJECTED           = 'rejected';
    case REVISION_REQUESTED = 'revision_requested';
    case REVISED            = 'revised';

    public function label(): string
    {
        return match($this) {
            self::DRAFT              => 'Draft',
            self::SUBMITTED          => 'Diajukan',
            self::VERIFIED           => 'Terverifikasi',
            self::APPROVED           => 'Disetujui',
            self::REJECTED           => 'Ditolak',
            self::REVISION_REQUESTED => 'Perlu Revisi',
            self::REVISED            => 'Sudah Direvisi',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT              => 'gray',
            self::SUBMITTED          => 'info',
            self::VERIFIED           => 'warning',
            self::APPROVED           => 'success',
            self::REJECTED           => 'danger',
            self::REVISION_REQUESTED => 'warning',
            self::REVISED            => 'info',
        };
    }

    public function canEdit(): bool
    {
        return in_array($this, [self::DRAFT, self::REVISED]);
    }

    public function canSubmit(): bool
    {
        return in_array($this, [self::DRAFT, self::REVISED]);
    }

    public function canVerify(): bool
    {
        return $this === self::SUBMITTED;
    }

    public function canApprove(): bool
    {
        return $this === self::VERIFIED;
    }

    public function canReject(): bool
    {
        return in_array($this, [self::SUBMITTED, self::VERIFIED]);
    }

    public function canRequestRevision(): bool
    {
        return $this === self::APPROVED;
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [
                $status->value => $status->label()
            ])
            ->toArray();
    }
}
