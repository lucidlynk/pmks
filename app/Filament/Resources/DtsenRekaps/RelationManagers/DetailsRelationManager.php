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
                TextColumn::make('kecamatan')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kelurahan')
                    ->label('Kelurahan/Desa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jumlah_keluarga')
                    ->label('Jml KK')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('jumlah_individu')
                    ->label('Jml Jiwa')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('desil1_keluarga')
                    ->label('D1 KK')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('desil1_individu')
                    ->label('D1 Jiwa')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('desil2_keluarga')
                    ->label('D2 KK')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('desil2_individu')
                    ->label('D2 Jiwa')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('desil3_keluarga')
                    ->label('D3 KK')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('desil3_individu')
                    ->label('D3 Jiwa')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('desil4_keluarga')
                    ->label('D4 KK')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('desil4_individu')
                    ->label('D4 Jiwa')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('desil5_keluarga')
                    ->label('D5 KK')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('desil5_individu')
                    ->label('D5 Jiwa')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('desil6_10_keluarga')
                    ->label('D6-10 KK')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('desil6_10_individu')
                    ->label('D6-10 Jiwa')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('belum_peringkat_keluarga')
                    ->label('Blm Peringkat KK')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('belum_peringkat_individu')
                    ->label('Blm Peringkat Jiwa')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('nonaktif_keluarga')
                    ->label('Nonaktif KK')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('nonaktif_individu')
                    ->label('Nonaktif Jiwa')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
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
