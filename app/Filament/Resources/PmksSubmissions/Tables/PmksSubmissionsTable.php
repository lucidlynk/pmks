<?php

namespace App\Filament\Resources\PmksSubmissions\Tables;

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

                // PERBAIKAN: Hapus visible() sementara untuk memastikan kolom muncul dulu
                TextColumn::make('disability_types')
                    ->label('Jenis Disabilitas')
                    ->badge()
                    ->separator(',')
                    ->color('warning')
                    ->placeholder('Bukan Disabilitas')
                    ->toggleable(), // Agar bisa di-on/off dari menu tabel

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

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'     => 'Draft',
                        'submitted' => 'Diajukan',
                        'approved'  => 'Disetujui',
                        'rejected'  => 'Ditolak',
                    ]),
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
