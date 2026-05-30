<?php

namespace App\Filament\Resources\KisPbiApbdImports\Pages;

use App\Filament\Resources\KisPbiApbdImports\KisPbiApbdImportResource;
use App\Jobs\Kis\KisPbiApbdParserJob;
use App\Models\KisPbiApbdImport;
use Filament\Resources\Pages\CreateRecord;

class CreateKisPbiApbdImport extends CreateRecord
{
    protected static string $resource = KisPbiApbdImportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by'] = auth()->id();
        $data['status']      = 'pending';
        return $data;
    }

    protected function afterCreate(): void
    {
        // Dispatch parser job ke queue 'imports'
        KisPbiApbdParserJob::dispatch($this->record->id)
            ->onQueue('imports');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'File CSV diterima, sedang diproses di background';
    }
}
