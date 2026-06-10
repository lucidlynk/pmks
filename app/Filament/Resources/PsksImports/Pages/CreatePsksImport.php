<?php

namespace App\Filament\Resources\PsksImports\Pages;

use App\Filament\Resources\PsksImports\PsksImportResource;
use App\Jobs\Psks\PsksImportParserJob;
use Filament\Resources\Pages\CreateRecord;

class CreatePsksImport extends CreateRecord
{
    protected static string $resource = PsksImportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status']     = 'pending';
        return $data;
    }

    protected function afterCreate(): void
    {
        PsksImportParserJob::dispatch($this->record->id)
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
