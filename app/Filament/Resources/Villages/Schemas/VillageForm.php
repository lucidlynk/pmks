<?php

namespace App\Filament\Resources\Villages\Schemas;

use App\Models\Kecamatan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VillageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('kecamatan_id')
                ->label('Kecamatan')
                ->options(Kecamatan::active()->pluck('name', 'id'))
                ->required()
                ->searchable(),

            TextInput::make('name')
                ->label('Nama Desa / Kelurahan')
                ->required()
                ->maxLength(255),

            TextInput::make('code')
                ->label('Kode Wilayah')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(20),

            Select::make('type')
                ->label('Jenis')
                ->options([
                    'desa'      => 'Desa',
                    'kelurahan' => 'Kelurahan',
                ])
                ->required(),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}