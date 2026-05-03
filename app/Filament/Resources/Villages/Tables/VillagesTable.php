<?php

namespace App\Filament\Resources\Villages\Tables;

use App\Models\Kecamatan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VillagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nama Desa / Kelurahan')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('kecamatan.name')
                    ->label('Kecamatan')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'desa'      => 'success',
                        'kelurahan' => 'info',
                        default     => 'gray',
                    }),

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
                SelectFilter::make('kecamatan_id')
                    ->label('Kecamatan')
                    ->options(Kecamatan::pluck('name', 'id')),

                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'desa'      => 'Desa',
                        'kelurahan' => 'Kelurahan',
                    ]),

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
            ->defaultSort('code');
    }
}