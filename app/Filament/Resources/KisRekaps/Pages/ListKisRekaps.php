<?php

namespace App\Filament\Resources\KisRekaps\Pages;

use App\Filament\Resources\KisRekaps\KisRekapResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKisRekaps extends ListRecords
{
    protected static string $resource = KisRekapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Rekap'),
        ];
    }
}
