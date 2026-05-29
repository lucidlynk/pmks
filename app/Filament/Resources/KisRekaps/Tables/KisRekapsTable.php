<?php

namespace App\Filament\Resources\KisRekaps\Tables;

use App\Models\KisRekap;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class KisRekapsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periode_label')
                    ->label('Periode')
                    ->getStateUsing(fn (KisRekap $record) => $record->periode_label)
                    ->sortable(query: fn ($query, $direction) =>
                        $query->orderBy('periode_tahun', $direction)
                              ->orderBy('periode_bulan', $direction)
                    )
                    ->searchable(query: fn ($query, $search) =>
                        $query->where('periode_tahun', 'like', "%{$search}%")
                    ),

                TextColumn::make('pbi_apbd')
                    ->label('PBI APBD')
                    ->numeric(thousandsSeparator: '.')
                    ->alignRight(),

                TextColumn::make('pbi_apbn')
                    ->label('PBI APBN')
                    ->numeric(thousandsSeparator: '.')
                    ->alignRight(),

                TextColumn::make('ppu')
                    ->label('PPU')
                    ->numeric(thousandsSeparator: '.')
                    ->alignRight(),

                TextColumn::make('pbpu')
                    ->label('PBPU')
                    ->numeric(thousandsSeparator: '.')
                    ->alignRight(),

                TextColumn::make('bp')
                    ->label('BP')
                    ->numeric(thousandsSeparator: '.')
                    ->alignRight(),

                TextColumn::make('total')
                    ->label('Total')
                    ->numeric(thousandsSeparator: '.')
                    ->alignRight()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('createdBy.name')
                    ->label('Diinput Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('periode_tahun')
                    ->label('Tahun')
                    ->options(function () {
                        return KisRekap::query()
                            ->distinct()
                            ->orderByDesc('periode_tahun')
                            ->pluck('periode_tahun', 'periode_tahun')
                            ->toArray();
                    }),

                SelectFilter::make('periode_bulan')
                    ->label('Bulan')
                    ->options(KisRekap::bulanOptions()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(fn ($query) =>
                $query->orderByDesc('periode_tahun')
                      ->orderByDesc('periode_bulan')
            )
            ->emptyStateHeading('Belum ada data rekap KIS')
            ->emptyStateDescription('Klik tombol "Tambah Rekap" untuk menambahkan data rekap KIS pertama.');
    }
}
