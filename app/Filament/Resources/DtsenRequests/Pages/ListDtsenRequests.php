<?php
namespace App\Filament\Resources\DtsenRequests\Pages;
use App\Filament\Resources\DtsenRequests\DtsenRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListDtsenRequests extends ListRecords
{
    protected static string $resource = DtsenRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
