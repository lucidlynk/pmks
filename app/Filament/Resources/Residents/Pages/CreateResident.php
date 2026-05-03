<?php

namespace App\Filament\Resources\Residents\Pages;

use App\Filament\Resources\Residents\ResidentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateResident extends CreateRecord
{
    protected static string $resource = ResidentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        if ($user->isOperatorDesa() && $user->village_id) {
            $data['village_id'] = $user->village_id;
        }
        return $data;
    }
}
