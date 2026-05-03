<?php

namespace App\Filament\Resources\PmksSubmissions\Pages;

use App\Filament\Resources\PmksSubmissions\PmksSubmissionResource;
use Filament\Resources\Pages\EditRecord;

class EditPmksSubmission extends EditRecord
{
    protected static string $resource = PmksSubmissionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['village_id']   = $this->record->village_id;
        $data['kecamatan_id'] = $this->record->village?->kecamatan_id;
        return $data;
    }
}
