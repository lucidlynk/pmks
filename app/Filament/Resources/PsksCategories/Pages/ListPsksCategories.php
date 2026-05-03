<?php

namespace App\Filament\Resources\PsksCategories\Pages;

use App\Filament\Resources\PsksCategories\PsksCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPsksCategories extends ListRecords
{
    protected static string $resource = PsksCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
