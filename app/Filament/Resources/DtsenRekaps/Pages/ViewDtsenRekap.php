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
        ]);
    }
}
