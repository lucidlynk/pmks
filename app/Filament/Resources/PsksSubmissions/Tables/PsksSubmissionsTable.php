<?php

namespace App\Filament\Resources\PsksSubmissions\Tables;

use App\Enums\BatchStatus;
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

                TextColumn::make('subject.name')
                    ->label('Nama Subjek')
                    ->searchable(false),

                // Status derive dari batch
                TextColumn::make('batch.status')
                    ->label('Status Batch')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof BatchStatus ? $state->label() : '-')
                    ->color(fn ($state) => $state instanceof BatchStatus ? $state->color() : 'gray'),

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
                    DeleteBulkAction::make()
                        ->authorize(fn () => true)
                        ->deselectRecordsAfterCompletion()
                        ->before(function ($records, $action) {
                            $locked = $records->filter(
                                fn ($r) => !$r->batch?->canBeEdited()
                            );
                            if ($locked->isNotEmpty()) {
                                $action->cancel();
                                \Filament\Notifications\Notification::make()
                                    ->title("Tidak dapat menghapus")
                                    ->body("Beberapa data sudah diajukan atau disetujui dan tidak bisa dihapus.")
                                    ->danger()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
