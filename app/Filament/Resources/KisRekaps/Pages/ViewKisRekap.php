<?php

namespace App\Filament\Resources\KisRekaps\Pages;

use App\Filament\Resources\KisRekaps\KisRekapResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewKisRekap extends ViewRecord
{
    protected static string $resource = KisRekapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Periode')
                ->columns(2)
                ->schema([
                    TextEntry::make('periode_label')
                        ->label('Periode')
                        ->getStateUsing(fn ($record) => $record->periode_label),

                    TextEntry::make('total')
                        ->label('Total Peserta')
                        ->numeric(thousandsSeparator: '.')
                        ->suffix(' jiwa'),
                ]),

            Section::make('Rincian Per Segmen')
                ->columns(3)
                ->schema([
                    TextEntry::make('pbi_apbd')
                        ->label('PBI APBD')
                        ->helperText('Penerima Bantuan Iuran APBD')
                        ->numeric(thousandsSeparator: '.')
                        ->suffix(' jiwa'),

                    TextEntry::make('pbi_apbn')
                        ->label('PBI APBN')
                        ->helperText('Penerima Bantuan Iuran APBN')
                        ->numeric(thousandsSeparator: '.')
                        ->suffix(' jiwa'),

                    TextEntry::make('ppu')
                        ->label('PPU')
                        ->helperText('Pekerja Penerima Upah')
                        ->numeric(thousandsSeparator: '.')
                        ->suffix(' jiwa'),

                    TextEntry::make('pbpu')
                        ->label('PBPU')
                        ->helperText('Pekerja Bukan Penerima Upah')
                        ->numeric(thousandsSeparator: '.')
                        ->suffix(' jiwa'),

                    TextEntry::make('bp')
                        ->label('BP')
                        ->helperText('Bukan Pekerja')
                        ->numeric(thousandsSeparator: '.')
                        ->suffix(' jiwa'),
                ]),

            Section::make('Informasi Pencatatan')
                ->columns(2)
                ->schema([
                    TextEntry::make('createdBy.name')
                        ->label('Diinput Oleh'),

                    TextEntry::make('created_at')
                        ->label('Tanggal Input')
                        ->dateTime('d M Y H:i'),

                    TextEntry::make('updatedBy.name')
                        ->label('Terakhir Diubah Oleh'),

                    TextEntry::make('updated_at')
                        ->label('Tanggal Perubahan')
                        ->dateTime('d M Y H:i'),
                ]),

        ]);
    }
}
