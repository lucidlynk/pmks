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
    // DESIL 1–5 TOTALS (1 query, cached per instance)
    // ================================================================

    private ?object $_desilTotals = null;

    private function desilTotals(): object
    {
        if ($this->_desilTotals === null) {
            $this->_desilTotals = $this->details()
                ->selectRaw('
                    COALESCE(SUM(desil1_keluarga), 0) AS d1_kk,
                    COALESCE(SUM(desil1_individu),  0) AS d1_jiwa,
                    COALESCE(SUM(desil2_keluarga), 0) AS d2_kk,
                    COALESCE(SUM(desil2_individu),  0) AS d2_jiwa,
                    COALESCE(SUM(desil3_keluarga), 0) AS d3_kk,
                    COALESCE(SUM(desil3_individu),  0) AS d3_jiwa,
                    COALESCE(SUM(desil4_keluarga), 0) AS d4_kk,
                    COALESCE(SUM(desil4_individu),  0) AS d4_jiwa,
                    COALESCE(SUM(desil5_keluarga), 0) AS d5_kk,
                    COALESCE(SUM(desil5_individu),  0) AS d5_jiwa
                ')
                ->first();
        }

        return $this->_desilTotals;
    }

    public function getTotalDesil1KeluargaAttribute(): int { return (int) $this->desilTotals()->d1_kk; }
    public function getTotalDesil1IndividuAttribute(): int  { return (int) $this->desilTotals()->d1_jiwa; }
    public function getTotalDesil2KeluargaAttribute(): int { return (int) $this->desilTotals()->d2_kk; }
    public function getTotalDesil2IndividuAttribute(): int  { return (int) $this->desilTotals()->d2_jiwa; }
    public function getTotalDesil3KeluargaAttribute(): int { return (int) $this->desilTotals()->d3_kk; }
    public function getTotalDesil3IndividuAttribute(): int  { return (int) $this->desilTotals()->d3_jiwa; }
    public function getTotalDesil4KeluargaAttribute(): int { return (int) $this->desilTotals()->d4_kk; }
    public function getTotalDesil4IndividuAttribute(): int  { return (int) $this->desilTotals()->d4_jiwa; }
    public function getTotalDesil5KeluargaAttribute(): int { return (int) $this->desilTotals()->d5_kk; }
    public function getTotalDesil5IndividuAttribute(): int  { return (int) $this->desilTotals()->d5_jiwa; }

    // Gabungan Desil 1+2+3+4+5
    public function getTotalDesil1Sampai5KeluargaAttribute(): int
    {
        $t = $this->desilTotals();
        return (int) ($t->d1_kk + $t->d2_kk + $t->d3_kk + $t->d4_kk + $t->d5_kk);
    }

    public function getTotalDesil1Sampai5IndividuAttribute(): int
    {
        $t = $this->desilTotals();
        return (int) ($t->d1_jiwa + $t->d2_jiwa + $t->d3_jiwa + $t->d4_jiwa + $t->d5_jiwa);
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
