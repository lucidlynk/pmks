<?php

namespace App\Filament\Resources\KisRekaps\Pages;

use App\Exports\KisRekapExport;
use App\Filament\Resources\KisRekaps\KisRekapResource;
use App\Models\KisRekap;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListKisRekaps extends ListRecords
{
    protected static string $resource = KisRekapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadExcel')
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    Select::make('tahun')
                        ->label('Tahun')
                        ->options(function () {
                            return KisRekap::query()
                                ->distinct()
                                ->orderByDesc('periode_tahun')
                                ->pluck('periode_tahun', 'periode_tahun')
                                ->toArray();
                        })
                        ->placeholder('Semua Tahun')
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    $tahun    = $data['tahun'] ?? null;
                    $filename = $tahun
                        ? "Rekap-KIS-{$tahun}.xlsx"
                        : 'Rekap-KIS-Semua.xlsx';

                    return Excel::download(new KisRekapExport($tahun), $filename);
                }),

            CreateAction::make()
                ->label('Tambah Rekap'),
        ];
    }
}
