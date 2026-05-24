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

class BulkApproveBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $batchIds,
        private int $userId,
    ) {}

    public function handle(): void
    {
        $approved = 0;
        $skipped = 0;

        foreach ($this->batchIds as $batchId) {
            $batch = SubmissionBatch::find($batchId);

            if (!$batch || !$batch->status->canApprove()) {
                $skipped++;
                continue;
            }

            $batch->update([
                'status'      => BatchStatus::APPROVED->value,
                'approved_by' => $this->userId,
                'approved_at' => now(),
            ]);
            $approved++;
        }

        $user = User::find($this->userId);
        if ($user) {
            Notification::make()
                ->title('Bulk Approve Batch Selesai')
                ->body("Berhasil disetujui: {$approved} batch. Dilewati: {$skipped}.")
                ->icon('heroicon-o-check-circle')
                ->iconColor('success')
                ->sendToDatabase($user);
        }

        AuditLogService::log(
            action: 'bulk_approve_batch',
            model: null,
            newValues: [
                'batch_ids' => $this->batchIds,
                'approved'  => $approved,
                'skipped'   => $skipped,
            ],
        );
    }
}
