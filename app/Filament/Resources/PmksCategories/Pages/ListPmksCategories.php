<?php

namespace App\Filament\Resources\PmksCategories\Pages;

use App\Filament\Resources\PmksCategories\PmksCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPmksCategories extends ListRecords
{
    protected static string $resource = PmksCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
