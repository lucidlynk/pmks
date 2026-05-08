<?php

namespace App\Filament\Resources\PsksSubmissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PsksSubmissionsTable
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

                TextColumn::make('village.kecamatan.name')
                    ->label('Kecamatan')
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Kategori PSKS')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('subject_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'person'      => 'info',
                        'institution' => 'warning',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'person'      => 'Individu',
                        'institution' => 'Lembaga',
                        default       => $state,
                    }),

                // Pakai relasi MorphTo, tidak lagi manual find()
                TextColumn::make('subject.name')
                    ->label('Nama Subjek')
                    ->searchable(false),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft'     => 'gray',
                        'submitted' => 'info',
                        'approved'  => 'success',
                        'rejected'  => 'danger',
                        default     => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Tgl Input')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori PSKS')
                    ->relationship('category', 'name'),

                SelectFilter::make('subject_type')
                    ->label('Jenis Subjek')
                    ->options([
                        'person'      => 'Individu',
                        'institution' => 'Lembaga',
                    ]),

                SelectFilter::make('village_id')
                    ->label('Desa')
                    ->relationship('village', 'name'),
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
