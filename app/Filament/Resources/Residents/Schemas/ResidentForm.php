<?php

namespace App\Filament\Resources\Residents\Schemas;

use App\Models\FamilyCard;
use App\Models\Kecamatan;
use App\Models\Village;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ResidentForm
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
                ->afterStateUpdated(function (callable $set) {
                    $set('village_id', null);
                    $set('family_card_id', null);
                })
                ->dehydrated(false)
                ->hidden($isOperatorDesa),

            Select::make('village_id')
                ->label('Desa / Kelurahan')
                ->required()
                ->searchable()
                ->live()
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
                ->afterStateUpdated(fn (callable $set) => $set('family_card_id', null))
                ->hidden($isOperatorDesa),

            Placeholder::make('village_info')
                ->label('Desa')
                ->content(fn () => $user?->village?->name ?? '-')
                ->visible($isOperatorDesa),

            Select::make('family_card_id')
                ->label('Kartu Keluarga (No. KK)')
                ->searchable()
                ->nullable()
                ->options(function (callable $get) use ($isOperatorDesa, $user) {
                    $villageId = $isOperatorDesa
                        ? $user->village_id
                        : $get('village_id');
                    if (!$villageId) return [];
                    return FamilyCard::active()
                        ->where('village_id', $villageId)
                        ->pluck('no_kk', 'id');
                })
                ->disabled(function (callable $get) use ($isOperatorDesa, $user) {
                    if ($isOperatorDesa) return false;
                    return !$get('village_id');
                })
                ->placeholder('Pilih KK (opsional)'),

            TextInput::make('nik')
                ->label('NIK')
                ->required()
                ->maxLength(16)
                ->minLength(16)
                ->placeholder('16 digit NIK')
                ->unique(
                    table: 'residents',
                    column: 'nik',
                    ignoreRecord: true,
                )
                ->validationMessages([
                    'unique' => 'NIK ini sudah terdaftar dalam sistem. Setiap penduduk harus memiliki NIK yang unik.',
                ]),

            TextInput::make('name')
                ->label('Nama Lengkap')
                ->required()
                ->maxLength(255),

            TextInput::make('birth_place')
                ->label('Tempat Lahir')
                ->required()
                ->maxLength(255),

            DatePicker::make('birth_date')
                ->label('Tanggal Lahir')
                ->required()
                ->maxDate(now()),

            Select::make('gender')
                ->label('Jenis Kelamin')
                ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])
                ->required(),

            Select::make('status_hubungan')
                ->label('Status Hubungan dalam KK')
                ->options([
                    'kepala_keluarga' => 'Kepala Keluarga',
                    'istri'           => 'Istri',
                    'anak'            => 'Anak',
                    'orang_tua'       => 'Orang Tua',
                    'lainnya'         => 'Lainnya',
                ])
                ->nullable(),

            TextInput::make('phone')
                ->label('Nomor HP')
                ->nullable()
                ->maxLength(20)
                ->tel(),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}
