<?php

namespace App\Jobs\Bansos;

use App\Models\BansosImport;
use App\Models\BansosMember;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BansosParserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 1;

    public function __construct(
        public readonly int $importId,
    ) {}

    public function handle(): void
    {
        $import = BansosImport::findOrFail($this->importId);

        if (!Storage::disk('local')->exists($import->file_path)) {
            $import->update([
                'status'        => 'failed',
                'finished_at'   => now(),
                'error_summary' => ['message' => 'File tidak ditemukan di storage.'],
            ]);
            return;
        }

        $import->update([
            'status'     => 'processing',
            'started_at' => now(),
        ]);

        try {
            // Cari import lama yang berpotensi digantikan (belum dihapus dulu)
            $oldImports = BansosImport::where('jenis_bansos', $import->jenis_bansos)
                ->where('status_bansos', $import->status_bansos)
                ->where('triwulan', $import->triwulan)
                ->where('tahun', $import->tahun)
                ->where('id', '!=', $import->id)
                ->where('status', 'done')
                ->get();

            // Baca dan validasi CSV DULU sebelum hapus data lama
            // Mencegah data loss jika file baru gagal dibuka atau kosong
            $fullPath = Storage::disk('local')->path($import->file_path);
            $handle   = fopen($fullPath, 'r');

            if (!$handle) {
                throw new \RuntimeException('Tidak bisa membuka file: ' . $fullPath);
            }

            // Bansos pakai separator pipe (|)
            $chunks    = [];
            $chunk     = [];
            $totalRows = 0;
            $isHeader  = true;

            while (($row = fgetcsv($handle, 0, '|')) !== false) {
                if ($isHeader) {
                    $isHeader = false;
                    continue;
                }

                if (!is_array($row) || empty(array_filter($row))) continue;

                $chunk[] = $row;
                $totalRows++;

                if (count($chunk) >= 500) {
                    $chunks[] = $chunk;
                    $chunk    = [];
                }
            }

            fclose($handle);

            if (!empty($chunk)) {
                $chunks[] = $chunk;
            }

            $import->update(['total_rows' => $totalRows]);

            if (empty($chunks)) {
                $import->update([
                    'status'        => 'failed',
                    'finished_at'   => now(),
                    'error_summary' => ['message' => 'File CSV kosong atau tidak ada data.'],
                ]);
                return;
            }

            // File valid dan ada data — baru hapus data lama (upload ulang / koreksi dari Kemensos)
            foreach ($oldImports as $old) {
                BansosMember::where('import_id', $old->id)->delete();
                $old->delete();
            }

            DB::reconnect();

            // Dispatch per 50 chunks untuk hindari MySQL timeout
            $chunkGroups = array_chunk($chunks, 50);
            $firstBatch  = true;
            $batchId     = null;

            foreach ($chunkGroups as $group) {
                $jobs = array_map(
                    fn ($chunk) => new BansosChunkJob($this->importId, $chunk),
                    $group
                );

                if ($firstBatch) {
                    $batch = Bus::batch($jobs)
                        ->name('Bansos Import #' . $this->importId)
                        ->allowFailures()
                        ->onQueue('imports')
                        ->then(function () use ($import) {
                            $import->update([
                                'status'      => 'done',
                                'finished_at' => now(),
                            ]);
                        })
                        ->catch(function () use ($import) {
                            $import->update(['status' => 'failed']);
                        })
                        ->finally(function () use ($import) {
                            if (!$import->fresh()->finished_at) {
                                $import->update(['finished_at' => now()]);
                            }
                        })
                        ->dispatch();

                    $batchId    = $batch->id;
                    $firstBatch = false;
                    $import->update(['batch_id' => $batchId]);
                    DB::reconnect();
                } else {
                    $existingBatch = Bus::findBatch($batchId);
                    if ($existingBatch) {
                        $existingBatch->add($jobs);
                    }
                    DB::reconnect();
                }
            }

        } catch (\Throwable $e) {
            Log::error('BansosParserJob error', [
                'import_id' => $this->importId,
                'error'     => $e->getMessage(),
            ]);

            $import->update([
                'status'        => 'failed',
                'finished_at'   => now(),
                'error_summary' => ['message' => $e->getMessage()],
            ]);
        }
    }
}
