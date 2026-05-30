<?php

namespace App\Filament\Resources\DinasSurats\Pages;

use App\Filament\Resources\DinasSurats\DinasSuratResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDinasSurat extends EditRecord
{
    protected static string $resource = DinasSuratResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Surat dinas berhasil diperbarui';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
