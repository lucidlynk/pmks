<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KisRekap extends Model
{
    protected $table = 'kis_rekaps';

    protected $fillable = [
        'periode_bulan',
        'periode_tahun',
        'pbi_apbd',
        'pbi_apbn',
        'ppu',
        'pbpu',
        'bp',
        'total',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'pbi_apbd'      => 'integer',
        'pbi_apbn'      => 'integer',
        'ppu'           => 'integer',
        'pbpu'          => 'integer',
        'bp'            => 'integer',
        'total'         => 'integer',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ================================================================
    // HOOKS
    // ================================================================

    protected static function booted(): void
    {
        // Auto-hitung total sebelum simpan
        static::saving(function (self $model) {
            $model->total = $model->pbi_apbd
                          + $model->pbi_apbn
                          + $model->ppu
                          + $model->pbpu
                          + $model->bp;
        });

        // Isi created_by & updated_by otomatis
        static::creating(function (self $model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }
        });

        static::updating(function (self $model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    // ================================================================
    // SCOPES
    // ================================================================

    public function scopeForYear($query, int $year)
    {
        return $query->where('periode_tahun', $year);
    }

    public function scopeForPeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)
                     ->where('periode_tahun', $tahun);
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

    public static function bulanOptions(): array
    {
        return [
            1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
            4  => 'April',    5  => 'Mei',       6  => 'Juni',
            7  => 'Juli',     8  => 'Agustus',   9  => 'September',
            10 => 'Oktober',  11 => 'November',  12 => 'Desember',
        ];
    }
}
