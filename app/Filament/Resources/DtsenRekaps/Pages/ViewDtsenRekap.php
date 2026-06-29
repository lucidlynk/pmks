<?php

namespace App\Filament\Resources\DtsenRekaps\Pages;

use App\Exports\DtsenRekapExport;
use App\Filament\Resources\DtsenRekaps\DtsenRekapResource;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Maatwebsite\Excel\Facades\Excel;

class ViewDtsenRekap extends ViewRecord
{
    protected static string $resource = DtsenRekapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_excel')
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $rekap    = $this->record;
                    $filename = 'DTSEN_' . str_replace(' ', '_', $rekap->periode) . '.xlsx';

                    return Excel::download(new DtsenRekapExport($rekap), $filename);
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Rekap')
                ->schema([
                    TextEntry::make('periode')
                        ->label('Periode'),
                    TextEntry::make('original_filename')
                        ->label('File'),
                    TextEntry::make('uploader.name')
                        ->label('Diupload Oleh'),
                    TextEntry::make('created_at')
                        ->label('Tanggal Upload')
                        ->dateTime('d M Y H:i'),
                    TextEntry::make('keterangan')
                        ->label('Keterangan')
                        ->placeholder('-'),
                ])
                ->columns(3),

            Section::make('Total Desil 1–5')
                ->description('Jumlah gabungan Desil 1 + Desil 2 + Desil 3 + Desil 4 + Desil 5')
                ->schema([
                    TextEntry::make('total_desil1_sampai5_keluarga')
                        ->label('Total KK (Desil 1–5)')
                        ->numeric(),
                    TextEntry::make('total_desil1_sampai5_individu')
                        ->label('Total Jiwa (Desil 1–5)')
                        ->numeric(),
                ])
                ->columns(2),

            Section::make('Rincian per Desil')
                ->description('Detail jumlah KK dan jiwa untuk masing-masing desil')
                ->schema([
                    TextEntry::make('total_desil1_keluarga')->label('Desil 1 — KK')->numeric(),
                    TextEntry::make('total_desil2_keluarga')->label('Desil 2 — KK')->numeric(),
                    TextEntry::make('total_desil3_keluarga')->label('Desil 3 — KK')->numeric(),
                    TextEntry::make('total_desil4_keluarga')->label('Desil 4 — KK')->numeric(),
                    TextEntry::make('total_desil5_keluarga')->label('Desil 5 — KK')->numeric(),
                    TextEntry::make('total_desil1_individu')->label('Desil 1 — Jiwa')->numeric(),
                    TextEntry::make('total_desil2_individu')->label('Desil 2 — Jiwa')->numeric(),
                    TextEntry::make('total_desil3_individu')->label('Desil 3 — Jiwa')->numeric(),
                    TextEntry::make('total_desil4_individu')->label('Desil 4 — Jiwa')->numeric(),
                    TextEntry::make('total_desil5_individu')->label('Desil 5 — Jiwa')->numeric(),
                ])
                ->columns(5)
                ->collapsed(),
        ]);
    }
}
