<?php

namespace App\Filament\Resources\PmksImports\Pages;

use App\Filament\Resources\PmksImports\PmksImportResource;
use App\Jobs\Pmks\PmksImportParserJob;
use Filament\Resources\Pages\CreateRecord;

class CreatePmksImport extends CreateRecord
{
    protected static string $resource = PmksImportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status']     = 'pending';
        return $data;
    }

    protected function afterCreate(): void
    {
        PmksImportParserJob::dispatch($this->record->id)
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
