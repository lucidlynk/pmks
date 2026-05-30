<?php

namespace App\Filament\Resources\DinasSurats\Pages;

use App\Filament\Resources\DinasSurats\DinasSuratResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDinasSurats extends ListRecords
{
    protected static string $resource = DinasSuratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Upload Surat')
                ->visible(fn () => auth()->user()?->hasRole('admin_dinsos')),
        ];
    }
}
