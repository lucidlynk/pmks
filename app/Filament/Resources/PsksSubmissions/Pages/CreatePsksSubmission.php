<?php

namespace App\Filament\Resources\PsksSubmissions\Pages;

use App\Filament\Resources\PsksSubmissions\PsksSubmissionResource;
use App\Models\SubmissionBatch;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePsksSubmission extends CreateRecord
{
    protected static string $resource = PsksSubmissionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['input_by'] = Auth::id();
        $data['status']   = 'draft';

        // Ambil village_id dari batch jika belum ada
        if (empty($data['village_id']) && !empty($data['batch_id'])) {
            $batch = SubmissionBatch::find($data['batch_id']);
            $data['village_id'] = $batch?->village_id;
        }

        return $data;
    }
}
