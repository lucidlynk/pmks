<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Bus;

class BansosImport extends Model
{
    protected $table = 'bansos_imports';

    protected $fillable = [
        'jenis_bansos',
        'status_bansos',
        'triwulan',
        'tahun',
        'original_filename',
        'file_path',
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
        'triwulan'       => 'integer',
        'tahun'          => 'integer',
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
        return $this->hasMany(BansosMember::class, 'import_id');
    }

    // ================================================================
    // HELPERS
    // ================================================================

    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis_bansos) {
            'pkh'     => 'PKH',
            'sembako' => 'Sembako',
            default   => strtoupper($this->jenis_bansos),
        };
    }

    public function getStatusBansosLabelAttribute(): string
    {
        return match ($this->status_bansos) {
            'sudah_si'         => 'Sudah SI',
            'sudah_salur'      => 'Sudah Salur',
            'sudah_transaksi'  => 'Sudah Transaksi',
            default            => $this->status_bansos,
        };
    }

    public function getTriwulanLabelAttribute(): string
    {
        return 'TW' . $this->triwulan . ' ' . $this->tahun;
    }

    public function getPeriodeLabelAttribute(): string
    {
        return $this->jenis_label . ' — ' . $this->status_bansos_label . ' — TW' . $this->triwulan . ' ' . $this->tahun;
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
    // STATIC HELPERS
    // ================================================================

    public static function jenisOptions(): array
    {
        return [
            'pkh'     => 'PKH',
            'sembako' => 'Sembako',
        ];
    }

    public static function statusBansosOptions(): array
    {
        return [
            'sudah_si'        => 'Sudah SI',
            'sudah_salur'     => 'Sudah Salur',
            'sudah_transaksi' => 'Sudah Transaksi',
        ];
    }

    public static function triwulanOptions(): array
    {
        return [
            1 => 'Triwulan 1 (Jan-Mar)',
            2 => 'Triwulan 2 (Apr-Jun)',
            3 => 'Triwulan 3 (Jul-Sep)',
            4 => 'Triwulan 4 (Okt-Des)',
        ];
    }
}
