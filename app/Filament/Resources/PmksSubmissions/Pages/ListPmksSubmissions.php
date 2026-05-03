<?php

namespace App\Filament\Resources\PmksSubmissions\Pages;

use App\Exports\PmksSubmissionExport;
use App\Filament\Resources\PmksSubmissions\PmksSubmissionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListPmksSubmissions extends ListRecords
{
    protected static string $resource = PmksSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    TextInput::make('period_year')
                        ->label('Tahun Periode')
                        ->numeric()
                        ->default(now()->year)
                        ->nullable(),

                    Select::make('village_id')
                        ->label('Desa (opsional)')
                        ->options(\App\Models\Village::active()->pluck('name', 'id'))
                        ->searchable()
                        ->nullable()
                        ->hidden(fn () => auth()->user()?->isOperatorDesa()),
                ])
                ->action(function (array $data) {
                    $user = auth()->user();

                    $export = new PmksSubmissionExport(
                        villageId:  $user->isOperatorDesa()
                            ? $user->village_id
                            : ($data['village_id'] ?? null),
                        periodYear: $data['period_year'] ?? null,
                    );

                    $filename = 'data-pmks-' . ($data['period_year'] ?? 'semua') . '.xlsx';

                    return Excel::download($export, $filename);
                }),
        ];
    }
}
