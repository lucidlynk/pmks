<?php

namespace App\Filament\Resources\FamilyCards\Pages;

use App\Filament\Resources\FamilyCards\FamilyCardResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateFamilyCard extends CreateRecord
{
    protected static string $resource = FamilyCardResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        if ($user->isOperatorDesa() && $user->village_id) {
            $data['village_id'] = $user->village_id;
        }
        return $data;
    }
}
