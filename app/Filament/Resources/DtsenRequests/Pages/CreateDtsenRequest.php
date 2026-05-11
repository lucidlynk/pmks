<?php

namespace App\Filament\Resources\DtsenRequests\Pages;

use App\Enums\DtsenStatus;
use App\Filament\Resources\DtsenRequests\DtsenRequestResource;
use App\Models\DtsenRequest;
use Filament\Resources\Pages\CreateRecord;

class CreateDtsenRequest extends CreateRecord
{
    protected static string $resource = DtsenRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['reference_number'] = DtsenRequest::generateReferenceNumber();
        $data['village_id']       = auth()->user()->village_id;
        $data['user_id']          = auth()->id();
        $data['status']           = DtsenStatus::DRAFT->value;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
