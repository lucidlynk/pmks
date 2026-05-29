<?php

namespace App\Filament\Resources\KisRekaps\Schemas;

use App\Models\KisRekap;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KisRekapForm
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

            Section::make('Jumlah Peserta Per Segmen')
                ->description('Input jumlah jiwa per segmen kepesertaan JKN-KIS bulan ini.')
                ->columns(2)
                ->schema([
                    TextInput::make('pbi_apbd')
                        ->label('PBI APBD')
                        ->helperText('Penerima Bantuan Iuran dari APBD Daerah')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->minValue(0)
                        ->suffix('jiwa'),

                    TextInput::make('pbi_apbn')
                        ->label('PBI APBN')
                        ->helperText('Penerima Bantuan Iuran dari APBN Pusat')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->minValue(0)
                        ->suffix('jiwa'),

                    TextInput::make('ppu')
                        ->label('PPU')
                        ->helperText('Pekerja Penerima Upah')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->minValue(0)
                        ->suffix('jiwa'),

                    TextInput::make('pbpu')
                        ->label('PBPU')
                        ->helperText('Pekerja Bukan Penerima Upah')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->minValue(0)
                        ->suffix('jiwa'),

                    TextInput::make('bp')
                        ->label('BP')
                        ->helperText('Bukan Pekerja')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->minValue(0)
                        ->suffix('jiwa'),
                ]),

        ]);
    }
}
