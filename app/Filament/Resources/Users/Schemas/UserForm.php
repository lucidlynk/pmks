<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Models\Kecamatan;
use App\Models\Village;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Lengkap')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            TextInput::make('password')
                ->label('Password')
                ->password()
                ->required(fn (string $operation) => $operation === 'create')
                ->minLength(8)
                ->dehydrated(fn ($state) => filled($state))
                ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                ->placeholder(fn (string $operation) => $operation === 'edit'
                    ? 'Kosongkan jika tidak ingin mengubah'
                    : 'Minimal 8 karakter'),

            Select::make('roles')
                ->label('Role')
                ->options(collect(UserRole::cases())
                    ->mapWithKeys(fn (UserRole $role) => [
                        $role->value => $role->label()
                    ]))
                ->required()
                ->searchable()
                ->live(),

            Select::make('kecamatan_id')
                ->label('Kecamatan')
                ->options(Kecamatan::active()->pluck('name', 'id'))
                ->searchable()
                ->live()
                ->afterStateUpdated(fn (callable $set) => $set('village_id', null))
                ->dehydrated(false)
                ->visible(fn (callable $get) => $get('roles') === UserRole::OPERATOR_DESA->value),

            Select::make('village_id')
                ->label('Desa / Kelurahan')
                ->options(function (callable $get) {
                    $kecamatanId = $get('kecamatan_id');
                    if (!$kecamatanId) return [];
                    return Village::active()
                        ->where('kecamatan_id', $kecamatanId)
                        ->pluck('name', 'id');
                })
                ->searchable()
                ->disabled(fn (callable $get) => !$get('kecamatan_id'))
                ->placeholder(fn (callable $get) => !$get('kecamatan_id')
                    ? 'Pilih kecamatan dulu'
                    : 'Pilih desa')
                ->visible(fn (callable $get) => $get('roles') === UserRole::OPERATOR_DESA->value)
                ->required(fn (callable $get) => $get('roles') === UserRole::OPERATOR_DESA->value),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}
