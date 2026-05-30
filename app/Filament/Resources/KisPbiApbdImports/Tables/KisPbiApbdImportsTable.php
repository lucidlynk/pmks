<?php

namespace App\Filament\Resources\KisPbiApbdImports\Tables;

use App\Models\KisPbiApbdImport;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class KisPbiApbdImportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periode_label')
                    ->label('Periode')
                    ->getStateUsing(fn (KisPbiApbdImport $record) => $record->periode_label)
                    ->sortable(query: fn ($query, $direction) =>
                        $query->orderBy('periode_tahun', $direction)
                              ->orderBy('periode_bulan', $direction)
                    ),

                TextColumn::make('original_filename')
                    ->label('File')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->original_filename),

                TextColumn::make('status_label')
                    ->label('Status')
                    ->getStateUsing(fn (KisPbiApbdImport $record) => $record->status_label)
                    ->badge()
                    ->color(fn (KisPbiApbdImport $record) => $record->status_color),

                TextColumn::make('total_rows')
                    ->label('Total Baris')
                    ->numeric(thousandsSeparator: '.')
                    ->alignRight()
                    ->placeholder('-'),

                TextColumn::make('processed_rows')
                    ->label('Berhasil')
                    ->numeric(thousandsSeparator: '.')
                    ->alignRight()
                    ->color('success'),

                TextColumn::make('failed_rows')
                    ->label('Gagal')
                    ->numeric(thousandsSeparator: '.')
                    ->alignRight()
                    ->color('danger'),

                TextColumn::make('uploadedBy.name')
                    ->label('Diupload Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Waktu Upload')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('finished_at')
                    ->label('Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'    => 'Menunggu',
                        'processing' => 'Sedang Diproses',
                        'done'       => 'Selesai',
                        'failed'     => 'Gagal',
                    ]),

                SelectFilter::make('periode_tahun')
                    ->label('Tahun')
                    ->options(function () {
                        return KisPbiApbdImport::query()
                            ->distinct()
                            ->orderByDesc('periode_tahun')
                            ->pluck('periode_tahun', 'periode_tahun')
                            ->toArray();
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada import CSV')
            ->emptyStateDescription('Klik tombol "Upload CSV" untuk mengimport data PBI APBD.');
    }
}
