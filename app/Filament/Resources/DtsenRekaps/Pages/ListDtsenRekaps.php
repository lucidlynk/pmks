<?php

namespace App\Filament\Resources\DtsenRekaps\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\DtsenRekaps\DtsenRekapResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDtsenRekaps extends ListRecords
{
    protected static string $resource = DtsenRekapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Upload Rekap DTSEN')
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(
                    fn () => auth()->user()?->hasRole(UserRole::ADMIN_DINSOS->value)
                ),
        ];
    }
}
