<?php

namespace App\Filament\Resources\SubmissionBatches\Pages;

use App\Filament\Resources\SubmissionBatches\SubmissionBatchResource;
use App\Models\SubmissionBatch;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSubmissionBatch extends CreateRecord
{
    protected static string $resource = SubmissionBatchResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        $data['submitted_by'] = $user->id;
        $data['status']       = 'draft';

        // Operator Desa: village_id otomatis dari user
        if ($user->isOperatorDesa() && $user->village_id) {
            $data['village_id'] = $user->village_id;
        }

        return $data;
    }

    protected function beforeCreate(): void
    {
        $user      = Auth::user();
        $villageId = $user->isOperatorDesa()
            ? $user->village_id
            : ($this->data['village_id'] ?? null);

        if (!$villageId) return;

        $exists = SubmissionBatch::where('village_id', $villageId)
            ->where('period_year', $this->data['period_year'])
            ->exists();

        if ($exists) {
            $this->halt();
            Notification::make()
                ->title('Batch sudah ada')
                ->body('Pengajuan untuk desa dan tahun ini sudah ada.')
                ->danger()
                ->send();
        }
    }
}
