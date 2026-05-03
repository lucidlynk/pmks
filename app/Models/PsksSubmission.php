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
        'status',
        'notes',
        'input_by',
    ];

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

    public function subject(): Resident|Institution|null
    {
        if ($this->subject_type === 'person') {
            return Resident::find($this->subject_id);
        }

        return Institution::find($this->subject_id);
    }

    public function inputBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}