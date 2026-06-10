<?php

namespace App\Filament\Resources\PmksImports\Pages;

use App\Filament\Resources\PmksImports\PmksImportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPmksImports extends ListRecords
{
    protected static string $resource = PmksImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Import CSV'),
        ];
    }
}
