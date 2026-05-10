<?php

namespace App\Filament\Resources\PmksSubmissions\Pages;

use App\Filament\Resources\PmksSubmissions\PmksSubmissionResource;
use App\Models\SubmissionBatch;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePmksSubmission extends CreateRecord
{
    protected static string $resource = PmksSubmissionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $data['input_by'] = $user->id;

        if (empty($data['village_id']) && !empty($data['batch_id'])) {
            $batch = SubmissionBatch::find($data['batch_id']);
            $data['village_id'] = $batch?->village_id;
        }

        if ($user->isOperatorDesa() && $user->village_id) {
            $data['village_id'] = $user->village_id;
        }

        return $data;
    }
}
