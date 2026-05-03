<?php

namespace App\Filament\Resources\Kecamatans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class KecamatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Kecamatan')
                ->required()
                ->maxLength(255),

            TextInput::make('code')
                ->label('Kode')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(10),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}