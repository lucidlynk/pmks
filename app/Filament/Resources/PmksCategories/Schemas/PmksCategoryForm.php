<?php

namespace App\Filament\Resources\PmksCategories\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PmksCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Kategori')
                ->columns(2)
                ->schema([
                    TextInput::make('code')
                        ->label('Kode')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(10)
                        ->placeholder('PMKS-01'),

                    TextInput::make('name')
                        ->label('Nama Kategori')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->nullable()
                        ->rows(3)
                        ->columnSpanFull(),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ]),

            Section::make('Aturan Validasi')
                ->description('Kosongkan jika tidak ada pembatasan untuk kategori ini.')
                ->columns(2)
                ->schema([
                    TextInput::make('min_age')
                        ->label('Usia Minimum')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(150)
                        ->nullable()
                        ->suffix('tahun')
                        ->placeholder('Kosongkan jika tidak ada'),

                    TextInput::make('max_age')
                        ->label('Usia Maksimum')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(999)
                        ->nullable()
                        ->suffix('tahun')
                        ->placeholder('999 untuk tanpa batas atas'),

                    Select::make('gender_restriction')
                        ->label('Pembatasan Gender')
                        ->options([
                            'L' => 'Laki-laki',
                            'P' => 'Perempuan',
                        ])
                        ->nullable()
                        ->placeholder('Semua gender')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
