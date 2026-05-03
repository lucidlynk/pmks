<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmksSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'batch_id',
        'village_id',
        'resident_id',
        'category_id',
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

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PmksCategory::class, 'category_id');
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