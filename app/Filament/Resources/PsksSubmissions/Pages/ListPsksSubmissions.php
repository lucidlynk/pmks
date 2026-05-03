<?php

namespace App\Filament\Resources\PsksSubmissions\Pages;

use App\Exports\PsksSubmissionExport;
use App\Filament\Resources\PsksSubmissions\PsksSubmissionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListPsksSubmissions extends ListRecords
{
    protected static string $resource = PsksSubmissionResource::class;

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

                    Select::make('subject_type')
                        ->label('Jenis Subjek (opsional)')
                        ->options([
                            'person'      => 'Individu',
                            'institution' => 'Lembaga',
                        ])
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

                    $export = new PsksSubmissionExport(
                        villageId:   $user->isOperatorDesa()
                            ? $user->village_id
                            : ($data['village_id'] ?? null),
                        periodYear:  $data['period_year'] ?? null,
                        subjectType: $data['subject_type'] ?? null,
                    );

                    $filename = 'data-psks-' . ($data['period_year'] ?? 'semua') . '.xlsx';

                    return Excel::download($export, $filename);
                }),
        ];
    }
}
