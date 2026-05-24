<?php

namespace App\Jobs;

use App\Enums\BatchStatus;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Services\AuditLogService;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BulkCreateBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $villageIds,
        private int $periodYear,
        private int $userId,
    ) {}

    public function handle(): void
    {
        $created = 0;
        $skipped = 0;

        foreach ($this->villageIds as $villageId) {
            $exists = SubmissionBatch::where('village_id', $villageId)
                ->where('period_year', $this->periodYear)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            SubmissionBatch::create([
                'village_id'   => $villageId,
                'period_year'  => $this->periodYear,
                'submitted_by' => $this->userId,
                'status'       => BatchStatus::DRAFT->value,
            ]);
            $created++;
        }

        // Kirim notifikasi ke user yang memicu job
        $user = User::find($this->userId);
        if ($user) {
            Notification::make()
                ->title('Bulk Create Batch Selesai')
                ->body("Berhasil dibuat: {$created} batch. Dilewati (sudah ada): {$skipped}.")
                ->icon('heroicon-o-check-circle')
                ->iconColor('success')
                ->sendToDatabase($user);
        }

        // Audit log
        AuditLogService::log(
            action: 'bulk_create_batch',
            model: null,
            newValues: [
                'period_year'    => $this->periodYear,
                'total_villages' => count($this->villageIds),
                'created'        => $created,
                'skipped'        => $skipped,
            ],
        );
    }
}
