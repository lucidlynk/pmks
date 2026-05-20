<?php

namespace App\Filament\Resources\DtsenRekaps\Pages;

use App\Filament\Resources\DtsenRekaps\DtsenRekapResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewDtsenRekap extends ViewRecord
{
    protected static string $resource = DtsenRekapResource::class;

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
