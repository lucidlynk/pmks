<?php

namespace App\Filament\Resources\SubmissionBatches\Schemas;

use App\Models\Kecamatan;
use App\Models\Village;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class SubmissionBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();

        return $schema->components([
            // Kecamatan — disembunyikan untuk Operator Desa
            Select::make('kecamatan_id')
                ->label('Kecamatan')
                ->options(Kecamatan::active()->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->live()
                ->afterStateUpdated(fn (callable $set) => $set('village_id', null))
                ->dehydrated(false)
                ->hidden(fn () => Auth::user()?->isOperatorDesa()),

            // Desa — disembunyikan untuk Operator Desa (otomatis dari user)
            Select::make('village_id')
                ->label('Desa / Kelurahan')
                ->required()
                ->searchable()
                ->options(function (callable $get) {
                    $user = Auth::user();

                    // Operator Desa: hanya desanya sendiri
                    if ($user?->isOperatorDesa() && $user->village_id) {
                        return Village::where('id', $user->village_id)
                            ->pluck('name', 'id');
                    }

                    $kecamatanId = $get('kecamatan_id');
                    if (!$kecamatanId) return [];
                    return Village::active()
                        ->where('kecamatan_id', $kecamatanId)
                        ->pluck('name', 'id');
                })
                ->disabled(fn (callable $get) =>
                    !Auth::user()?->isOperatorDesa() && !$get('kecamatan_id')
                )
                ->default(fn () =>
                    Auth::user()?->isOperatorDesa()
                        ? Auth::user()->village_id
                        : null
                )
                ->hidden(fn () => Auth::user()?->isOperatorDesa()),

            // Info desa untuk Operator Desa (read-only)
            Placeholder::make('village_info')
                ->label('Desa')
                ->content(fn () => Auth::user()?->village?->name ?? '-')
                ->visible(fn () => Auth::user()?->isOperatorDesa()),

            TextInput::make('period_year')
                ->label('Tahun Periode')
                ->required()
                ->numeric()
                ->minValue(2020)
                ->maxValue(2099)
                ->default(now()->year),
        ]);
    }
}
