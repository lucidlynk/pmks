<?php

namespace App\Filament\Resources\BansosImports\Pages;

use App\Filament\Resources\BansosImports\BansosImportResource;
use App\Jobs\Bansos\BansosParserJob;
use Filament\Resources\Pages\CreateRecord;

class CreateBansosImport extends CreateRecord
{
    protected static string $resource = BansosImportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by'] = auth()->id();
        $data['status']      = 'pending';
        return $data;
    }

    protected function afterCreate(): void
    {
        BansosParserJob::dispatch($this->record->id)
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
