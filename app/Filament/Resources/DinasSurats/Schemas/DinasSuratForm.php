<?php

namespace App\Filament\Resources\DinasSurats\Schemas;

use App\Models\DinasSurat;
use App\Models\Kecamatan;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DinasSuratForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Informasi Surat')
                ->columns(2)
                ->schema([
                    TextInput::make('judul')
                        ->label('Judul Surat')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('nomor_surat')
                        ->label('Nomor Surat')
                        ->maxLength(100)
                        ->nullable()
                        ->placeholder('Contoh: 400/123/Dinsos/2026'),

                    DatePicker::make('tanggal_surat')
                        ->label('Tanggal Surat')
                        ->required()
                        ->native(false)
                        ->default(now()),

                    Select::make('kategori')
                        ->label('Kategori')
                        ->options(DinasSurat::kategoriOptions())
                        ->required()
                        ->native(false),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->helperText('Surat nonaktif tidak akan terlihat oleh operator desa')
                        ->default(true),

                    Textarea::make('deskripsi')
                        ->label('Deskripsi')
                        ->nullable()
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ]),

            Section::make('Target Penerima')
                ->columns(2)
                ->schema([
                    Select::make('target_scope')
                        ->label('Target')
                        ->options([
                            'semua'      => 'Semua Desa/Kelurahan',
                            'kecamatan'  => 'Kecamatan Tertentu',
                        ])
                        ->required()
                        ->native(false)
                        ->default('semua')
                        ->live(),

                    Select::make('kecamatan_ids')
                        ->label('Pilih Kecamatan')
                        ->multiple()
                        ->options(fn () => Kecamatan::active()->orderBy('name')->pluck('name', 'id'))
                        ->visible(fn ($get) => $get('target_scope') === 'kecamatan')
                        ->required(fn ($get) => $get('target_scope') === 'kecamatan')
                        ->nullable(),
                ]),

            Section::make('File Surat')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('Upload File Surat')
                        ->required()
                        ->disk('local')
                        ->directory('dinas-surats')
                        ->visibility('private')
                        ->acceptedFileTypes([
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ])
                        ->maxSize(20480)
                        ->storeFileNamesIn('original_filename')
                        ->helperText('Format: PDF, DOC, DOCX. Maks 20MB.')
                        ->columnSpanFull(),
                ]),

        ]);
    }
}
