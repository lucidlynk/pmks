<?php

namespace App\Filament\Resources\PmksSubmissions\Tables;

use App\Enums\BatchStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PmksSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch.period_year')
                    ->label('Tahun')
                    ->sortable(),

                TextColumn::make('village.name')
                    ->label('Desa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('resident.nik')
                    ->label('NIK')
                    ->searchable(),

                TextColumn::make('resident.name')
                    ->label('Nama Penduduk')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('resident.gender')
                    ->label('JK')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'L' => 'info',
                        'P' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => $state === 'L' ? 'Laki-laki' : 'Perempuan'),

                TextColumn::make('category.name')
                    ->label('Kategori PMKS')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('disability_types')
                    ->label('Jenis Disabilitas')
                    ->badge()
                    ->separator(',')
                    ->color('warning')
                    ->placeholder('Bukan Disabilitas')
                    ->toggleable(),

                // Status derive dari batch
                TextColumn::make('batch.status')
                    ->label('Status Batch')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof BatchStatus ? $state->label() : '-')
                    ->color(fn ($state) => $state instanceof BatchStatus ? $state->color() : 'gray'),

                TextColumn::make('inputBy.name')
                    ->label('Diinput Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Tgl Input')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori PMKS')
                    ->relationship('category', 'name'),

                SelectFilter::make('village_id')
                    ->label('Desa')
                    ->relationship('village', 'name'),

                // Filter status via batch — query manual
                SelectFilter::make('batch_status')
                    ->label('Status Batch')
                    ->options(BatchStatus::options())
                    ->query(fn ($query, $state) =>
                        $state['value']
                            ? $query->whereHas('batch', fn ($q) => $q->where('status', $state['value']))
                            : $query
                    ),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record) => $record->batch?->canBeEdited()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
