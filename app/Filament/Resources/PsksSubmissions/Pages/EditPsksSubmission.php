<?php

namespace App\Filament\Resources\PsksSubmissions\Pages;

use App\Filament\Resources\PsksSubmissions\PsksSubmissionResource;
use Filament\Resources\Pages\EditRecord;

class EditPsksSubmission extends EditRecord
{
    protected static string $resource = PsksSubmissionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['village_id']   = $this->record->village_id;
        $data['kecamatan_id'] = $this->record->village?->kecamatan_id;
        return $data;
    }
}
