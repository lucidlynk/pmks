<?php

namespace App\Filament\Resources\BansosImports\Schemas;

use App\Models\BansosImport;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BansosImportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Periode & Jenis Bansos')
                ->columns(2)
                ->schema([
                    Select::make('jenis_bansos')
                        ->label('Jenis Bansos')
                        ->options(BansosImport::jenisOptions())
                        ->required()
                        ->native(false),

                    Select::make('status_bansos')
                        ->label('Status Bansos')
                        ->options(BansosImport::statusBansosOptions())
                        ->required()
                        ->native(false),

                    Select::make('triwulan')
                        ->label('Triwulan')
                        ->options(BansosImport::triwulanOptions())
                        ->required()
                        ->native(false),

                    TextInput::make('tahun')
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
                        ->label('File CSV Bansos')
                        ->required()
                        ->disk('local')
                        ->directory('bansos-imports')
                        ->visibility('private')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv'])
                        ->maxSize(51200)
                        ->storeFileNamesIn('original_filename')
                        ->helperText('Format kolom: NAMA_PENERIMA|NIK|NOKK|PENYALURAN_OLEH|BANSOS|PROP_NAME|KAB_NAME|KEC_NAME|KEL_NAME|ALAMAT|status|kode_batch. Separator: pipe (|). Maks 50MB.')
                        ->columnSpanFull(),
                ]),

        ]);
    }
}
