<?php

namespace App\Filament\Resources\PsksCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PsksCategoriesTable
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
                    ->label('Nama Kategori')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                TextColumn::make('subject_type')
                    ->label('Jenis Subjek')
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

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('subject_type')
                    ->label('Jenis Subjek')
                    ->options([
                        'person'      => 'Individu / Jiwa',
                        'institution' => 'Lembaga',
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