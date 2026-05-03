<?php

namespace App\Models;

use App\Enums\BatchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmissionBatch extends Model
{
    protected $fillable = [
        'village_id', 'submitted_by', 'period_year', 'status',
        'letter_file_path', 'letter_uploaded_at',
        'draft_letter_path', 'draft_generated_at',
        'verified_by', 'verified_at', 'verification_notes',
        'approved_by', 'approved_at', 'rejection_notes',
        'finalized_at',
    ];

    protected $casts = [
        'status'              => BatchStatus::class,
        'letter_uploaded_at'  => 'datetime',
        'draft_generated_at'  => 'datetime',
        'verified_at'         => 'datetime',
        'approved_at'         => 'datetime',
        'finalized_at'        => 'datetime',
        'period_year'         => 'integer',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function pmksSubmissions(): HasMany
    {
        return $this->hasMany(PmksSubmission::class, 'batch_id');
    }

    public function psksSubmissions(): HasMany
    {
        return $this->hasMany(PsksSubmission::class, 'batch_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(BatchRevision::class, 'batch_id');
    }

    public function scopeForVillage($query, int $villageId)
    {
        return $query->where('village_id', $villageId);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('period_year', $year);
    }

    public function isDraft(): bool { return $this->status === BatchStatus::DRAFT; }
    public function isSubmitted(): bool { return $this->status === BatchStatus::SUBMITTED; }
    public function isApproved(): bool { return $this->status === BatchStatus::APPROVED; }
    public function canBeEdited(): bool { return $this->status->canEdit(); }
    public function canBeSubmitted(): bool { return $this->status->canSubmit(); }
}
