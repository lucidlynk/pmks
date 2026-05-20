<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DtsenRekap extends Model
{
    use SoftDeletes;

    protected $table = 'dtsen_rekaps';

    protected $fillable = [
        'bulan',
        'tahun',
        'file_path',
        'original_filename',
        'keterangan',
        'uploaded_by',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DtsenRekapDetail::class);
    }

    // ================================================================
    // ACCESSORS
    // ================================================================

    public function getNamaBulanAttribute(): string
    {
        return self::bulanOptions()[$this->bulan] ?? '-';
    }

    public function getPeriodeAttribute(): string
    {
        return "{$this->nama_bulan} {$this->tahun}";
    }

    public function getTotalKeluargaAttribute(): int
    {
        return (int) $this->details()->sum('jumlah_keluarga');
    }

    public function getTotalIndividuAttribute(): int
    {
        return (int) $this->details()->sum('jumlah_individu');
    }

    public function getJumlahKecamatanAttribute(): int
    {
        return $this->details()->distinct('kecamatan')->count('kecamatan');
    }

    public function getJumlahKelurahanAttribute(): int
    {
        return $this->details()->count();
    }

    // ================================================================
    // STATIC HELPERS
    // ================================================================

    public static function bulanOptions(): array
    {
        return [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }
}
