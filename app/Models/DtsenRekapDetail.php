<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtsenRekapDetail extends Model
{
    protected $table = 'dtsen_rekap_details';

    protected $fillable = [
        'dtsen_rekap_id',
        'kecamatan',
        'kelurahan',
        'jumlah_keluarga',
        'jumlah_individu',
        'desil1_keluarga',
        'desil1_individu',
        'desil2_keluarga',
        'desil2_individu',
        'desil3_keluarga',
        'desil3_individu',
        'desil4_keluarga',
        'desil4_individu',
        'desil5_keluarga',
        'desil5_individu',
        'desil6_10_keluarga',
        'desil6_10_individu',
        'belum_peringkat_keluarga',
        'belum_peringkat_individu',
        'nonaktif_keluarga',
        'nonaktif_individu',
    ];

    protected $casts = [
        'jumlah_keluarga'          => 'integer',
        'jumlah_individu'          => 'integer',
        'desil1_keluarga'          => 'integer',
        'desil1_individu'          => 'integer',
        'desil2_keluarga'          => 'integer',
        'desil2_individu'          => 'integer',
        'desil3_keluarga'          => 'integer',
        'desil3_individu'          => 'integer',
        'desil4_keluarga'          => 'integer',
        'desil4_individu'          => 'integer',
        'desil5_keluarga'          => 'integer',
        'desil5_individu'          => 'integer',
        'desil6_10_keluarga'       => 'integer',
        'desil6_10_individu'       => 'integer',
        'belum_peringkat_keluarga' => 'integer',
        'belum_peringkat_individu' => 'integer',
        'nonaktif_keluarga'        => 'integer',
        'nonaktif_individu'        => 'integer',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    public function rekapDtsen(): BelongsTo
    {
        return $this->belongsTo(DtsenRekap::class, 'dtsen_rekap_id');
    }

    // ================================================================
    // ACCESSORS
    // ================================================================

    public function getTotalDesil1Sampai4KeluargaAttribute(): int
    {
        return $this->desil1_keluarga
             + $this->desil2_keluarga
             + $this->desil3_keluarga
             + $this->desil4_keluarga;
    }

    public function getTotalDesil1Sampai4IndividuAttribute(): int
    {
        return $this->desil1_individu
             + $this->desil2_individu
             + $this->desil3_individu
             + $this->desil4_individu;
    }
}
