<?php

namespace App\Filament\Resources\BansosImports\Pages;

use App\Filament\Resources\BansosImports\BansosImportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBansosImports extends ListRecords
{
    protected static string $resource = BansosImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Upload CSV Bansos'),
        ];
    }
}
