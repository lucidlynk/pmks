<?php

namespace App\Filament\Resources\KisRekaps\Pages;

use App\Filament\Resources\KisRekaps\KisRekapResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKisRekap extends EditRecord
{
    protected static string $resource = KisRekapResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Rekap KIS berhasil diperbarui';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
