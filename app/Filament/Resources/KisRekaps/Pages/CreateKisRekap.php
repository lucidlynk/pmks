<?php

namespace App\Filament\Resources\KisRekaps\Pages;

use App\Filament\Resources\KisRekaps\KisRekapResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKisRekap extends CreateRecord
{
    protected static string $resource = KisRekapResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Rekap KIS berhasil disimpan';
    }
}
