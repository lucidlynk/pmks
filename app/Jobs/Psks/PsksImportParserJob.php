<?php

namespace App\Jobs\Psks;

use App\Models\PsksImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PsksImportParserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 1;

    private const CHUNK_SIZE      = 100;
    private const CHUNK_GROUP_MAX = 50;

    public function __construct(
        public readonly int $importId,
    ) {}

    public function handle(): void
    {
        $import = PsksImport::findOrFail($this->importId);

        if (!Storage::disk('local')->exists($import->file_path)) {
            $import->update([
                'status'        => 'failed',
                'finished_at'   => now(),
                'error_summary' => ['File tidak ditemukan di storage.'],
            ]);
            return;
        }

        $import->update([
            'status'     => 'processing',
            'started_at' => now(),
        ]);

        try {
            $fullPath = Storage::disk('local')->path($import->file_path);
            $handle   = fopen($fullPath, 'r');

            if (!$handle) {
                throw new \RuntimeException('Tidak bisa membuka file: ' . $fullPath);
            }

            $chunksWithOffset = [];
            $chunk            = [];
            $totalRows        = 0;
            $startRow         = 2;
            $isHeader         = true;

            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                if ($isHeader) {
                    $isHeader = false;
                    continue;
                }

                if (!is_array($row) || empty(array_filter($row))) continue;

                $chunk[] = $row;
                $totalRows++;

                if (count($chunk) >= self::CHUNK_SIZE) {
                    $chunksWithOffset[] = ['rows' => $chunk, 'startRow' => $startRow];
                    $startRow += count($chunk);
                    $chunk = [];
                }
            }

            fclose($handle);

            if (!empty($chunk)) {
                $chunksWithOffset[] = ['rows' => $chunk, 'startRow' => $startRow];
            }

            $import->update(['total_rows' => $totalRows]);

            if (empty($chunksWithOffset)) {
                $import->update([
                    'status'        => 'failed',
                    'finished_at'   => now(),
                    'error_summary' => ['File CSV kosong atau tidak ada data setelah header.'],
                ]);
                return;
            }

            $chunkGroups = array_chunk($chunksWithOffset, self::CHUNK_GROUP_MAX);
            $firstBatch  = true;
            $jobBatchId  = null;

            foreach ($chunkGroups as $group) {
                $jobs = array_map(
                    fn ($item) => new PsksImportChunkJob($this->importId, $item['rows'], $item['startRow']),
                    $group
                );

                if ($firstBatch) {
                    $jobBatch = Bus::batch($jobs)
                        ->name('PSKS Import #' . $this->importId)
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

                    $jobBatchId = $jobBatch->id;
                    $firstBatch = false;
                    $import->update(['job_batch_id' => $jobBatchId]);
                    DB::reconnect();
                } else {
                    $existingBatch = Bus::findBatch($jobBatchId);
                    if ($existingBatch) {
                        $existingBatch->add($jobs);
                    }
                    DB::reconnect();
                }
            }

        } catch (\Throwable $e) {
            Log::error('PsksImportParserJob error', [
                'import_id' => $this->importId,
                'error'     => $e->getMessage(),
            ]);

            $import->update([
                'status'        => 'failed',
                'finished_at'   => now(),
                'error_summary' => [$e->getMessage()],
            ]);
        }
    }
}
