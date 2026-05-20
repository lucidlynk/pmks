<?php

namespace App\Filament\Resources\DtsenRekaps\Schemas;

use App\Models\DtsenRekap;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DtsenRekapForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('bulan')
                ->label('Bulan')
                ->options(DtsenRekap::bulanOptions())
                ->required()
                ->searchable(),

            TextInput::make('tahun')
                ->label('Tahun')
                ->required()
                ->numeric()
                ->minValue(2020)
                ->maxValue(2099)
                ->default(now()->year),

            FileUpload::make('file_path')
                ->label('File Rekap DTSEN')
                ->required()
                ->disk('local')
                ->directory('dtsen-rekaps')
                ->visibility('private')
                ->acceptedFileTypes([
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                    'text/csv',
                    'text/plain',
                ])
                ->maxSize(10240)
                ->storeFileNamesIn('original_filename')
                ->helperText('Format: .xlsx, .xls, atau .csv (semicolon/comma). Maks 10MB.'),

            TextInput::make('keterangan')
                ->label('Keterangan (opsional)')
                ->maxLength(255)
                ->nullable()
                ->placeholder('Contoh: Rekap DTSEN Kabupaten Buleleng Mei 2026'),
        ]);
    }
}
