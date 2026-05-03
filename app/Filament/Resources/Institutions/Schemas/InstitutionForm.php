<?php

namespace App\Filament\Resources\Institutions\Schemas;

use App\Models\Kecamatan;
use App\Models\Village;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class InstitutionForm
{
    public static function configure(Schema $schema): Schema
    {
        $user           = Auth::user();
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

            TextInput::make('name')
                ->label('Nama Lembaga')
                ->required()
                ->maxLength(255),

            Select::make('type')
                ->label('Jenis Lembaga')
                ->options([
                    'karang_taruna'  => 'Karang Taruna',
                    'pkk'            => 'PKK',
                    'lks'            => 'LKS (Lembaga Kesejahteraan Sosial)',
                    'lksa'           => 'LKSA (Lembaga Kesejahteraan Sosial Anak)',
                    'lembaga_sosial' => 'Lembaga Sosial',
                    'orsos'          => 'Organisasi Sosial',
                    'other'          => 'Lainnya',
                ])
                ->required()
                ->searchable(),

            TextInput::make('registration_number')
                ->label('Nomor Registrasi')
                ->nullable()
                ->maxLength(100),

            TextInput::make('address')
                ->label('Alamat')
                ->nullable()
                ->maxLength(500),

            TextInput::make('contact_person')
                ->label('Nama Kontak')
                ->nullable()
                ->maxLength(255),

            TextInput::make('phone')
                ->label('Nomor Telepon')
                ->nullable()
                ->maxLength(20)
                ->tel(),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}
