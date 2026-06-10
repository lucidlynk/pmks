<?php

namespace App\Filament\Resources\PsksImports\Pages;

use App\Filament\Resources\PsksImports\PsksImportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPsksImports extends ListRecords
{
    protected static string $resource = PsksImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Import CSV'),
        ];
    }
}
