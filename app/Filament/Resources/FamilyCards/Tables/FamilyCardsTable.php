<?php

namespace App\Filament\Resources\FamilyCards\Tables;

use App\Models\Kecamatan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FamilyCardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_kk')
                    ->label('Nomor KK')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kepala_keluarga')
                    ->label('Kepala Keluarga')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('village.name')
                    ->label('Desa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('village.kecamatan.name')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('residents_count')
                    ->label('Anggota')
                    ->counts('residents')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('village.kecamatan_id')
                    ->label('Kecamatan')
                    ->relationship('village.kecamatan', 'name'),

                SelectFilter::make('village_id')
                    ->label('Desa')
                    ->relationship('village', 'name'),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}