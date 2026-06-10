<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Bus;

class PmksImport extends Model
{
    protected $table = 'pmks_imports';

    protected $fillable = [
        'submission_batch_id',
        'original_filename',
        'file_path',
        'status',
        'job_batch_id',
        'total_rows',
        'success_rows',
        'failed_rows',
        'error_summary',
        'created_by',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'total_rows'   => 'integer',
        'success_rows' => 'integer',
        'failed_rows'  => 'integer',
        'error_summary' => 'array',
        'started_at'   => 'datetime',
        'finished_at'  => 'datetime',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class, 'submission_batch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ================================================================
    // HELPERS
    // ================================================================

    public function getProgressAttribute(): int
    {
        if (!$this->job_batch_id) return 0;
        if ($this->status === 'done') return 100;
        if ($this->status === 'failed') return 0;

        try {
            $batch = Bus::findBatch($this->job_batch_id);
            return $batch ? $batch->progress() : 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'Menunggu',
            'processing' => 'Sedang Diproses',
            'done'       => 'Selesai',
            'failed'     => 'Gagal',
            default      => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'gray',
            'processing' => 'warning',
            'done'       => 'success',
            'failed'     => 'danger',
            default      => 'gray',
        };
    }

    public function getBatchLabelAttribute(): string
    {
        $batch = $this->submissionBatch;
        if (!$batch) return '-';

        $village = $batch->village?->name ?? '-';
        $year    = $batch->period_year ?? '-';

        return "{$village} — {$year}";
    }

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isProcessing(): bool { return $this->status === 'processing'; }
    public function isDone(): bool       { return $this->status === 'done'; }
    public function isFailed(): bool     { return $this->status === 'failed'; }
    public function isFinished(): bool   { return in_array($this->status, ['done', 'failed']); }
}
