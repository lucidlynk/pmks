<?php

namespace App\Filament\Resources\DinasSurats\Pages;

use App\Filament\Resources\DinasSurats\DinasSuratResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateDinasSurat extends CreateRecord
{
    protected static string $resource = DinasSuratResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by'] = auth()->id();

        // Simpan ukuran file
        if (!empty($data['file_path'])) {
            $data['file_size'] = Storage::disk('local')->size($data['file_path']) ?? 0;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Surat dinas berhasil diupload';
    }
}
