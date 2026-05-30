<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Bus;

class KisPbiApbdImport extends Model
{
    protected $table = 'kis_pbi_apbd_imports';

    protected $fillable = [
        'original_filename',
        'file_path',
        'periode_bulan',
        'periode_tahun',
        'status',
        'batch_id',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'error_summary',
        'uploaded_by',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'periode_bulan'  => 'integer',
        'periode_tahun'  => 'integer',
        'total_rows'     => 'integer',
        'processed_rows' => 'integer',
        'failed_rows'    => 'integer',
        'error_summary'  => 'array',
        'started_at'     => 'datetime',
        'finished_at'    => 'datetime',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(KisPbiApbdMember::class, 'import_id');
    }

    // ================================================================
    // HELPERS
    // ================================================================

    public function getPeriodeLabelAttribute(): string
    {
        $bulanLabel = [
            1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
            4  => 'April',    5  => 'Mei',       6  => 'Juni',
            7  => 'Juli',     8  => 'Agustus',   9  => 'September',
            10 => 'Oktober',  11 => 'November',  12 => 'Desember',
        ];

        return ($bulanLabel[$this->periode_bulan] ?? $this->periode_bulan)
             . ' ' . $this->periode_tahun;
    }

    public function getProgressAttribute(): int
    {
        if (!$this->batch_id) return 0;
        if ($this->status === 'done') return 100;
        if ($this->status === 'failed') return 0;

        try {
            $batch = Bus::findBatch($this->batch_id);
            return $batch ? $batch->progress() : 0;
        } catch (\Throwable $e) {
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

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isProcessing(): bool { return $this->status === 'processing'; }
    public function isDone(): bool       { return $this->status === 'done'; }
    public function isFailed(): bool     { return $this->status === 'failed'; }
    public function isFinished(): bool   { return in_array($this->status, ['done', 'failed']); }

    // ================================================================
    // SCOPES
    // ================================================================

    public function scopeForPeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)
                     ->where('periode_tahun', $tahun);
    }
}
