<?php
namespace App\Filament\Resources\DtsenRequests\Pages;
use App\Filament\Resources\DtsenRequests\DtsenRequestResource;
use Filament\Resources\Pages\EditRecord;
class EditDtsenRequest extends EditRecord
{
    protected static string $resource = DtsenRequestResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
    protected function getHeaderActions(): array
    {
        return [];
    }
}
