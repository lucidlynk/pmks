<?php

namespace App\Filament\Resources\KisPbiApbdImports\Schemas;

use App\Models\KisRekap;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KisPbiApbdImportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Periode')
                ->columns(2)
                ->schema([
                    Select::make('periode_bulan')
                        ->label('Bulan')
                        ->options(KisRekap::bulanOptions())
                        ->required()
                        ->native(false),

                    TextInput::make('periode_tahun')
                        ->label('Tahun')
                        ->numeric()
                        ->required()
                        ->minValue(2020)
                        ->maxValue(2099)
                        ->default(now()->year),
                ]),

            Section::make('File CSV')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('File CSV PBI APBD')
                        ->required()
                        ->disk('local')
                        ->directory('kis-imports')
                        ->visibility('private')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv'])
                        ->maxSize(51200)
                        ->storeFileNamesIn('original_filename')
                        ->helperText('Format kolom: PSNOKA, NIK, NAMA, SEGMEN, BULAN, TAHUN. Maks 50MB.')
                        ->columnSpanFull(),
                ]),

        ]);
    }
}
