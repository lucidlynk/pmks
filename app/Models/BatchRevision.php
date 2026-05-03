<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchRevision extends Model
{
    protected $fillable = [
        'batch_id', 'requested_by', 'approved_by',
        'reason', 'status', 'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class, 'batch_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
}
