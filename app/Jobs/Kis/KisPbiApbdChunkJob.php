<?php

namespace App\Jobs\Kis;

use App\Models\KisPbiApbdImport;
use App\Models\KisPbiApbdMember;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KisPbiApbdChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 3;

    // Map nama bulan ke angka (support Indonesia)
    private const BULAN_MAP = [
        'januari'   => 1,  'january'   => 1,  '1'  => 1,
        'februari'  => 2,  'february'  => 2,  '2'  => 2,
        'maret'     => 3,  'march'     => 3,  '3'  => 3,
        'april'     => 4,                      '4'  => 4,
        'mei'       => 5,  'may'       => 5,  '5'  => 5,
        'juni'      => 6,  'june'      => 6,  '6'  => 6,
        'juli'      => 7,  'july'      => 7,  '7'  => 7,
        'agustus'   => 8,  'august'    => 8,  '8'  => 8,
        'september' => 9,                      '9'  => 9,
        'oktober'   => 10, 'october'   => 10, '10' => 10,
        'november'  => 11,                     '11' => 11,
        'desember'  => 12, 'december'  => 12, '12' => 12,
    ];

    public function __construct(
        public readonly int   $importId,
        public readonly array $rows,
    ) {}

    private function parseBulan(string $value): ?int
    {
        $key = strtolower(trim($value));
        return self::BULAN_MAP[$key] ?? null;
    }

    private function normalizeSegmen(string $value): string
    {
        $value = strtoupper(trim($value));
        // Normalize: APBD → PBI APBD, APBN → PBI APBN
        if ($value === 'APBD') return 'PBI APBD';
        if ($value === 'APBN') return 'PBI APBN';
        return $value;
    }

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) return;

        $import    = KisPbiApbdImport::findOrFail($this->importId);
        $processed = 0;
        $failed    = 0;
        $errors    = [];

        foreach ($this->rows as $row) {
            try {
                if (count($row) < 6) {
                    $failed++;
                    $errors[] = "Baris tidak lengkap: " . implode(';', $row);
                    continue;
                }

                $psnoka = trim($row[0]);
                $nik    = trim($row[1]);
                $nama   = trim($row[2]);
                $segmen = $this->normalizeSegmen($row[3]);
                $bulan  = $this->parseBulan($row[4]);
                $tahun  = (int) trim($row[5]);

                if (empty($nik)) {
                    $failed++;
                    $errors[] = "NIK kosong pada PSNOKA: {$psnoka}";
                    continue;
                }

                if ($bulan === null) {
                    $failed++;
                    $errors[] = "Bulan tidak valid ({$row[4]}) untuk NIK: {$nik}";
                    continue;
                }

                if ($tahun < 2000 || $tahun > 2099) {
                    $failed++;
                    $errors[] = "Tahun tidak valid ({$tahun}) untuk NIK: {$nik}";
                    continue;
                }

                KisPbiApbdMember::updateOrInsert(
                    [
                        'nik'           => $nik,
                        'periode_bulan' => $bulan,
                        'periode_tahun' => $tahun,
                    ],
                    [
                        'import_id'  => $this->importId,
                        'psnoka'     => $psnoka,
                        'nama'       => $nama,
                        'segmen'     => $segmen,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $processed++;

            } catch (\Throwable $e) {
                $failed++;
                $errors[] = "Error pada NIK {$nik}: " . $e->getMessage();
                Log::warning('KisPbiApbdChunkJob row error', [
                    'import_id' => $this->importId,
                    'row'       => $row,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        DB::table('kis_pbi_apbd_imports')
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
