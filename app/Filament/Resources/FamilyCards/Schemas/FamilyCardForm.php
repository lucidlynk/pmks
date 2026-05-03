<?php

namespace App\Filament\Resources\FamilyCards\Schemas;

use App\Models\Kecamatan;
use App\Models\Village;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class FamilyCardForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isOperatorDesa = $user?->isOperatorDesa();

        return $schema->components([
            Select::make('kecamatan_id')
                ->label('Kecamatan')
                ->options(Kecamatan::active()->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->live()
                ->afterStateUpdated(fn (callable $set) => $set('village_id', null))
                ->dehydrated(false)
                ->hidden($isOperatorDesa),

            Select::make('village_id')
                ->label('Desa / Kelurahan')
                ->required()
                ->searchable()
                ->options(function (callable $get) use ($isOperatorDesa, $user) {
                    if ($isOperatorDesa && $user->village_id) {
                        return Village::where('id', $user->village_id)->pluck('name', 'id');
                    }
                    $kecamatanId = $get('kecamatan_id');
                    if (!$kecamatanId) return [];
                    return Village::active()
                        ->where('kecamatan_id', $kecamatanId)
                        ->pluck('name', 'id');
                })
                ->disabled(fn (callable $get) => !$isOperatorDesa && !$get('kecamatan_id'))
                ->default($isOperatorDesa ? $user->village_id : null)
                ->hidden($isOperatorDesa),

            Placeholder::make('village_info')
                ->label('Desa')
                ->content(fn () => $user?->village?->name ?? '-')
                ->visible($isOperatorDesa),

            TextInput::make('no_kk')
                ->label('Nomor KK')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(16)
                ->minLength(16)
                ->placeholder('16 digit nomor KK'),

            TextInput::make('kepala_keluarga')
                ->label('Nama Kepala Keluarga')
                ->required()
                ->maxLength(255),

            TextInput::make('address')
                ->label('Alamat')
                ->required()
                ->maxLength(500),

            TextInput::make('rt')
                ->label('RT')
                ->nullable()
                ->maxLength(5),

            TextInput::make('rw')
                ->label('RW')
                ->nullable()
                ->maxLength(5),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}
