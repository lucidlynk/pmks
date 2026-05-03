<?php

namespace App\Filament\Resources\SubmissionBatches\Pages;

use App\Filament\Resources\SubmissionBatches\SubmissionBatchResource;
use Filament\Resources\Pages\EditRecord;

class EditSubmissionBatch extends EditRecord
{
    protected static string $resource = SubmissionBatchResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['kecamatan_id'] = $this->record->village->kecamatan_id ?? null;
        return $data;
    }
}
