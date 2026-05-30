<?php

namespace App\Filament\Resources\KisPbiApbdImports\Pages;

use App\Filament\Resources\KisPbiApbdImports\KisPbiApbdImportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKisPbiApbdImports extends ListRecords
{
    protected static string $resource = KisPbiApbdImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Upload CSV'),
        ];
    }
}
