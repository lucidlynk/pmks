<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BansosMember extends Model
{
    protected $table = 'bansos_members';

    protected $fillable = [
        'import_id',
        'nama_penerima',
        'nik',
        'nokk',
        'penyaluran_oleh',
        'jenis_bansos',
        'kec_name',
        'kel_name',
        'status_bansos',
        'kode_batch',
        'triwulan',
        'tahun',
    ];

    protected $casts = [
        'triwulan' => 'integer',
        'tahun'    => 'integer',
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    public function import(): BelongsTo
    {
        return $this->belongsTo(BansosImport::class, 'import_id');
    }

    // ================================================================
    // SCOPES
    // ================================================================

    public function scopeForPeriode($query, string $jenis, int $triwulan, int $tahun)
    {
        return $query->where('jenis_bansos', $jenis)
                     ->where('triwulan', $triwulan)
                     ->where('tahun', $tahun);
    }

    public function scopeForStatus($query, string $status)
    {
        return $query->where('status_bansos', $status);
    }

    // ================================================================
    // AGREGAT
    // ================================================================

    public static function agregatPerKecamatanDesa(string $jenis, int $triwulan, int $tahun): array
    {
        $results = static::where('jenis_bansos', $jenis)
            ->where('triwulan', $triwulan)
            ->where('tahun', $tahun)
            ->selectRaw('kec_name, kel_name, status_bansos, COUNT(*) as jumlah')
            ->groupBy('kec_name', 'kel_name', 'status_bansos')
            ->orderBy('kec_name')
            ->orderBy('kel_name')
            ->get();

        // Reshape ke format per kecamatan > desa > status
        $data = [];
        foreach ($results as $row) {
            $kec  = $row->kec_name ?? 'Tidak Diketahui';
            $kel  = $row->kel_name ?? 'Tidak Diketahui';
            $stat = strtolower(str_replace(' ', '_', $row->status_bansos));

            if (!isset($data[$kec])) $data[$kec] = [];
            if (!isset($data[$kec][$kel])) {
                $data[$kec][$kel] = [
                    'sudah_si'        => 0,
                    'sudah_salur'     => 0,
                    'sudah_transaksi' => 0,
                ];
            }

            $data[$kec][$kel][$stat] = $row->jumlah;
        }

        return $data;
    }
}
