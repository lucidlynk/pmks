<?php

namespace App\Filament\Resources\SubmissionBatches\Tables;

use App\Enums\BatchStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubmissionBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('village.name')
                    ->label('Desa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('village.kecamatan.name')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('period_year')
                    ->label('Tahun')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (BatchStatus $state) => $state->color())
                    ->formatStateUsing(fn (BatchStatus $state) => $state->label()),

                TextColumn::make('pmks_submissions_count')
                    ->label('PMKS')
                    ->counts('pmksSubmissions')
                    ->sortable(),

                TextColumn::make('psks_submissions_count')
                    ->label('PSKS')
                    ->counts('psksSubmissions')
                    ->sortable(),

                TextColumn::make('submittedBy.name')
                    ->label('Diajukan Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('verifiedBy.name')
                    ->label('Diverifikasi Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approvedBy.name')
                    ->label('Disetujui Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approved_at')
                    ->label('Tgl Disetujui')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(BatchStatus::options()),

                SelectFilter::make('period_year')
                    ->label('Tahun')
                    ->options(function () {
                        return collect(range(now()->year, 2020))
                            ->mapWithKeys(fn ($y) => [$y => $y])
                            ->toArray();
                    }),

                SelectFilter::make('village_id')
                    ->label('Desa')
                    ->relationship('village', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => $record->canBeEdited()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('period_year', 'desc');
    }
}
