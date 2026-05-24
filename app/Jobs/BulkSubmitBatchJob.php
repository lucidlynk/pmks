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

class BulkSubmitBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $batchIds,
        private int $userId,
    ) {}

    public function handle(): void
    {
        $submitted = 0;
        $skipped = 0;

        foreach ($this->batchIds as $batchId) {
            $batch = SubmissionBatch::find($batchId);

            if (!$batch || !$batch->status->canSubmit()) {
                $skipped++;
                continue;
            }

            $batch->update([
                'status' => BatchStatus::SUBMITTED->value,
            ]);
            $submitted++;
        }

        $user = User::find($this->userId);
        if ($user) {
            Notification::make()
                ->title('Bulk Submit Batch Selesai')
                ->body("Berhasil diajukan: {$submitted} batch. Dilewati: {$skipped}.")
                ->icon('heroicon-o-paper-airplane')
                ->iconColor('info')
                ->sendToDatabase($user);
        }

        AuditLogService::log(
            action: 'bulk_submit_batch',
            model: null,
            newValues: [
                'batch_ids' => $this->batchIds,
                'submitted' => $submitted,
                'skipped'   => $skipped,
            ],
        );
    }
}
