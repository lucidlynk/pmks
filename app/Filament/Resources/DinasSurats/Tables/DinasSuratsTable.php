<?php

namespace App\Filament\Resources\DinasSurats\Tables;

use App\Models\DinasSurat;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DinasSuratsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_surat')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('judul')
                    ->label('Judul Surat')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->judul)
                    ->searchable(),

                TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('kategori_label')
                    ->label('Kategori')
                    ->getStateUsing(fn (DinasSurat $record) => $record->kategori_label)
                    ->badge()
                    ->color(fn (DinasSurat $record) => $record->kategori_color),

                TextColumn::make('target_scope')
                    ->label('Target')
                    ->formatStateUsing(fn ($state) => $state === 'semua' ? 'Semua Desa' : 'Kecamatan Tertentu')
                    ->badge()
                    ->color(fn ($state) => $state === 'semua' ? 'success' : 'warning'),

                TextColumn::make('file_size_label')
                    ->label('Ukuran')
                    ->getStateUsing(fn (DinasSurat $record) => $record->file_size_label),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('uploadedBy.name')
                    ->label('Diupload Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Waktu Upload')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options(DinasSurat::kategoriOptions()),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),

                SelectFilter::make('target_scope')
                    ->label('Target')
                    ->options([
                        'semua'     => 'Semua Desa',
                        'kecamatan' => 'Kecamatan Tertentu',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('admin_dinsos')),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('admin_dinsos')),
            ])
            ->defaultSort('tanggal_surat', 'desc')
            ->emptyStateHeading('Belum ada surat dinas')
            ->emptyStateDescription('Klik tombol "Upload Surat" untuk menambahkan surat dinas.');
    }
}
