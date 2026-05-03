<?php

namespace App\Filament\Resources\SubmissionBatches\Pages;

use App\Filament\Resources\SubmissionBatches\SubmissionBatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubmissionBatches extends ListRecords
{
    protected static string $resource = SubmissionBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
