<?php

namespace App\Jobs\Kis;

use App\Models\KisPbiApbdImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class KisPbiApbdParserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries   = 1;

    public function __construct(
        public readonly int $importId,
    ) {}

    public function handle(): void
    {
        $import = KisPbiApbdImport::findOrFail($this->importId);

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
            $fullPath = Storage::disk('local')->path($import->file_path);

            $handle = fopen($fullPath, 'r');
            if (!$handle) {
                throw new \RuntimeException('Tidak bisa membuka file: ' . $fullPath);
            }

            // Deteksi separator
            $firstLine = fgets($handle);
            $separator = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
            rewind($handle);

            $chunks    = [];
            $chunk     = [];
            $totalRows = 0;
            $isHeader  = true;

            while (($row = fgetcsv($handle, 0, $separator)) !== false) {
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
                    'error_summary' => ['message' => 'File CSV kosong.'],
                ]);
                return;
            }

            // Dispatch dalam kelompok 50 chunk sekaligus
            // untuk hindari MySQL timeout saat insert ratusan jobs
            $chunkGroups = array_chunk($chunks, 50);
            $firstBatch  = true;
            $batchId     = null;

            foreach ($chunkGroups as $group) {
                $jobs = array_map(
                    fn ($chunk) => new KisPbiApbdChunkJob($this->importId, $chunk),
                    $group
                );

                if ($firstBatch) {
                    // Batch pertama — buat batch dengan callbacks
                    $batch = Bus::batch($jobs)
                        ->name('KIS PBI APBD Import #' . $this->importId)
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

                    // Reconnect DB setelah operasi besar
                    DB::reconnect();
                } else {
                    // Group berikutnya — tambahkan ke batch yang sama
                    $existingBatch = Bus::findBatch($batchId);
                    if ($existingBatch) {
                        $existingBatch->add($jobs);
                    }

                    // Reconnect setiap group
                    DB::reconnect();
                }
            }

        } catch (\Throwable $e) {
            Log::error('KisPbiApbdParserJob error', [
                'import_id' => $this->importId,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            $import->update([
                'status'        => 'failed',
                'finished_at'   => now(),
                'error_summary' => ['message' => $e->getMessage()],
            ]);
        }
    }
}
