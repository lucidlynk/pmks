<?php

namespace App\Filament\Resources\PmksCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PmksCategoriesTable
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

                TextColumn::make('age_range')
                    ->label('Batasan Usia')
                    ->getStateUsing(fn ($record) => $record->ageLabel())
                    ->badge()
                    ->color('info'),

                TextColumn::make('gender_restriction')
                    ->label('Gender')
                    ->getStateUsing(fn ($record) => $record->genderLabel())
                    ->badge()
                    ->color(fn ($record) => match($record->gender_restriction) {
                        'L'     => 'info',
                        'P'     => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('pmks_submissions_count')
                    ->label('Jumlah Data')
                    ->counts('pmksSubmissions')
                    ->sortable(),
            ])
            ->filters([
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
