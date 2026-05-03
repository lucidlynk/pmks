<?php

namespace App\Filament\Resources\PmksCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PmksCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label('Kode')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(10)
                ->placeholder('PMKS-01'),

            TextInput::make('name')
                ->label('Nama Kategori')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->label('Deskripsi')
                ->nullable()
                ->rows(3),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}