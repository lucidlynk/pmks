<?php

namespace App\Filament\Resources\PmksSubmissions\Pages;

use App\Filament\Resources\PmksSubmissions\PmksSubmissionResource;
use App\Models\PmksCategory;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Rules\PmksAgeRule;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreatePmksSubmission extends CreateRecord
{
    protected static string $resource = PmksSubmissionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $data['input_by'] = $user->id;
        $data['status']   = 'draft';

        if (empty($data['village_id']) && !empty($data['batch_id'])) {
            $batch = SubmissionBatch::find($data['batch_id']);
            $data['village_id'] = $batch?->village_id;
        }

        if ($user->isOperatorDesa() && $user->village_id) {
            $data['village_id'] = $user->village_id;
        }

        return $data;
    }

    protected function beforeCreate(): void
    {
        $residentId = $this->data['resident_id'] ?? null;
        $categoryId = $this->data['category_id'] ?? null;

        if (!$residentId || !$categoryId) return;

        $resident = Resident::find($residentId);
        $category = PmksCategory::find($categoryId);

        if (!$resident || !$category) return;

        $rule = PmksAgeRule::getRulesForCategory($category->code);
        if (!$rule) return;

        $age = $resident->birth_date->age;

        if ($age < $rule['min'] || $age > $rule['max']) {
            $this->halt();
            Notification::make()
                ->title('Usia tidak sesuai kategori')
                ->body("Kategori {$category->name} hanya untuk usia {$rule['label']}. Penduduk ini berusia {$age} tahun.")
                ->danger()
                ->send();
        }
    }
}
