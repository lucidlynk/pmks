<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KisPbiApbdMember extends Model
{
    protected $table = 'kis_pbi_apbd_members';

    protected $fillable = [
        'import_id',
        'psnoka',
        'nik',
        'nama',
        'segmen',
        'periode_bulan',
        'periode_tahun',
    ];

    protected $casts = [
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    public function import(): BelongsTo
    {
        return $this->belongsTo(KisPbiApbdImport::class, 'import_id');
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

    // ================================================================
    // SCOPES
    // ================================================================

    public function scopeForPeriode($query, int $bulan, int $tahun)
    {
        return $query->where('periode_bulan', $bulan)
                     ->where('periode_tahun', $tahun);
    }

    public function scopeByNik($query, string $nik)
    {
        return $query->where('nik', $nik);
    }
}
