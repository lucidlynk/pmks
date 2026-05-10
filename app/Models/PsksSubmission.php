<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PsksSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'batch_id',
        'village_id',
        'category_id',
        'subject_type',
        'subject_id',
        'notes',
        'input_by',
    ];

    protected $morphMap = [
        'person'      => Resident::class,
        'institution' => Institution::class,
    ];

    // Derive status dari batch induk
    public function getStatusAttribute(): string
    {
        return $this->batch?->status->value ?? 'draft';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->batch?->status->label() ?? 'Draft';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->batch?->status->color() ?? 'gray';
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class, 'batch_id');
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PsksCategory::class, 'category_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'subject_type', 'subject_id');
    }

    public function inputBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_by');
    }
}
