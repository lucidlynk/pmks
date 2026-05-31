<?php

namespace App\Jobs\Bansos;

use App\Models\BansosImport;
use App\Models\BansosMember;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BansosChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 3;

    private const STATUS_MAP = [
        'sudah si'        => 'sudah_si',
        'sudah_si'        => 'sudah_si',
        'berhasil salur'  => 'sudah_salur',
        'sudah salur'     => 'sudah_salur',
        'sudah_salur'     => 'sudah_salur',
        'sudah transaksi' => 'sudah_transaksi',
        'sudah_transaksi' => 'sudah_transaksi',
    ];

    // Sembako bisa datang dengan nama: SEMBAKO, BPNT, BPNT SEMBAKO, dll
    private const JENIS_MAP = [
        'pkh'          => 'pkh',
        'sembako'      => 'sembako',
        'bpnt'         => 'sembako',
        'bpnt sembako' => 'sembako',
        'sembako bpnt' => 'sembako',
    ];

    public function __construct(
        public readonly int   $importId,
        public readonly array $rows,
    ) {}

    private function normalizeStatus(string $value): string
    {
        $key = strtolower(trim($value));
        return self::STATUS_MAP[$key] ?? $key;
    }

    private function normalizeJenis(string $value): string
    {
        $key = strtolower(trim($value));
        // Cek exact match dulu
        if (isset(self::JENIS_MAP[$key])) {
            return self::JENIS_MAP[$key];
        }
        // Cek contains
        if (str_contains($key, 'pkh')) return 'pkh';
        if (str_contains($key, 'sembako') || str_contains($key, 'bpnt')) return 'sembako';
        return $key;
    }

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) return;

        $import    = BansosImport::findOrFail($this->importId);
        $processed = 0;
        $failed    = 0;
        $errors    = [];
        $inserts   = [];

        foreach ($this->rows as $row) {
            try {
                if (count($row) < 11) {
                    $failed++;
                    $errors[] = "Baris tidak lengkap: " . implode('|', $row);
                    continue;
                }

                $nama     = trim($row[0]);
                $nik      = trim($row[1]);
                $nokk     = trim($row[2]);
                $penyalur = trim($row[3]);
                $jenis    = $this->normalizeJenis($row[4]);
                $kec      = trim($row[7]);
                $kel      = trim($row[8]);
                $status   = $this->normalizeStatus($row[10]);
                $kode     = isset($row[11]) ? trim($row[11]) : null;

                if (empty($nama)) {
                    $failed++;
                    $errors[] = "Nama kosong pada baris: " . implode('|', array_slice($row, 0, 5));
                    continue;
                }

                // Validasi jenis harus pkh atau sembako
                if (!in_array($jenis, ['pkh', 'sembako'])) {
                    $failed++;
                    $errors[] = "Jenis bansos tidak dikenal: {$row[4]} (nama: {$nama})";
                    continue;
                }

                $inserts[] = [
                    'import_id'       => $this->importId,
                    'nama_penerima'   => $nama,
                    'nik'             => $nik,
                    'nokk'            => $nokk ?: null,
                    'penyaluran_oleh' => $penyalur ?: null,
                    'jenis_bansos'    => $jenis,
                    'kec_name'        => $kec ?: null,
                    'kel_name'        => $kel ?: null,
                    'status_bansos'   => $status,
                    'kode_batch'      => $kode,
                    'triwulan'        => $import->triwulan,
                    'tahun'           => $import->tahun,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];

                $processed++;

            } catch (\Throwable $e) {
                $failed++;
                $errors[] = "Error: " . $e->getMessage();
                Log::warning('BansosChunkJob row error', [
                    'import_id' => $this->importId,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        if (!empty($inserts)) {
            foreach (array_chunk($inserts, 100) as $batch) {
                BansosMember::insert($batch);
            }
        }

        DB::table('bansos_imports')
            ->where('id', $this->importId)
            ->update([
                'processed_rows' => DB::raw("processed_rows + {$processed}"),
                'failed_rows'    => DB::raw("failed_rows + {$failed}"),
            ]);

        if (!empty($errors)) {
            $existing = $import->fresh()->error_summary ?? [];
            $import->update([
                'error_summary' => array_merge($existing, $errors),
            ]);
        }
    }
}
