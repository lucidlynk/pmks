<?php

namespace App\Filament\Resources\DtsenRekaps\RelationManagers;

use App\Models\DtsenRekapDetail;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';
    protected static ?string $title = 'Detail per Desa/Kelurahan';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kecamatan')->label('Kecamatan')->searchable()->sortable(),
                TextColumn::make('kelurahan')->label('Kelurahan/Desa')->searchable()->sortable(),
                TextColumn::make('jumlah_keluarga')->label('Jml Keluarga')->numeric()->sortable()->summarize(Sum::make()->label('Total')),
                TextColumn::make('jumlah_individu')->label('Jml Individu')->numeric()->sortable()->summarize(Sum::make()->label('Total')),
                TextColumn::make('desil1_keluarga')->label('D1 Kel')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('desil1_individu')->label('D1 Ind')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('desil2_keluarga')->label('D2 Kel')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('desil2_individu')->label('D2 Ind')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('desil3_keluarga')->label('D3 Kel')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('desil3_individu')->label('D3 Ind')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('desil4_keluarga')->label('D4 Kel')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('desil4_individu')->label('D4 Ind')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('desil5_keluarga')->label('D5 Kel')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('desil5_individu')->label('D5 Ind')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('desil6_10_keluarga')->label('D6-10 Kel')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('desil6_10_individu')->label('D6-10 Ind')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('belum_peringkat_keluarga')->label('Blm Peringkat Kel')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('belum_peringkat_individu')->label('Blm Peringkat Ind')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nonaktif_keluarga')->label('Nonaktif Kel')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nonaktif_individu')->label('Nonaktif Ind')->numeric()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kecamatan')
                    ->label('Kecamatan')
                    ->options(function () {
                        return DtsenRekapDetail::where('dtsen_rekap_id', $this->ownerRecord->id)
                            ->distinct()
                            ->pluck('kecamatan', 'kecamatan')
                            ->toArray();
                    })
                    ->searchable(),
            ])
            ->defaultSort('kecamatan')
            ->striped()
            ->paginated([25, 50, 100, 'all']);
    }
}
