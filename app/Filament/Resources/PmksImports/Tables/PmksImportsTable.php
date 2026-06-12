<?php

namespace App\Filament\Resources\PmksImports\Tables;

use App\Models\PmksImport;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PmksImportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('desa')
                    ->label('Desa')
                    ->getStateUsing(fn (PmksImport $record) => $record->isKabupatenMode()
                        ? 'Seluruh Kabupaten'
                        : ($record->submissionBatch?->village?->name ?? '-')
                    )
                    ->searchable(false)
                    ->sortable(false),

                TextColumn::make('kecamatan')
                    ->label('Kecamatan')
                    ->getStateUsing(fn (PmksImport $record) => $record->isKabupatenMode()
                        ? '-'
                        : ($record->submissionBatch?->village?->kecamatan?->name ?? '-')
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->getStateUsing(fn (PmksImport $record) => $record->isKabupatenMode()
                        ? $record->period_year
                        : $record->submissionBatch?->period_year
                    )
                    ->sortable(false),

                TextColumn::make('original_filename')
                    ->label('File')
                    ->limit(35)
                    ->tooltip(fn (PmksImport $record) => $record->original_filename),

                TextColumn::make('status_label')
                    ->label('Status')
                    ->getStateUsing(fn (PmksImport $record) => $record->status_label)
                    ->badge()
                    ->color(fn (PmksImport $record) => $record->status_color),

                TextColumn::make('total_rows')
                    ->label('Total')
                    ->numeric(thousandsSeparator: '.')
                    ->alignRight()
                    ->placeholder('-'),

                TextColumn::make('success_rows')
                    ->label('Berhasil')
                    ->numeric(thousandsSeparator: '.')
                    ->alignRight()
                    ->color('success'),

                TextColumn::make('failed_rows')
                    ->label('Gagal')
                    ->numeric(thousandsSeparator: '.')
                    ->alignRight()
                    ->color('danger'),

                TextColumn::make('createdBy.name')
                    ->label('Diupload Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Waktu Upload')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Import')
                    ->options([
                        'pending'    => 'Menunggu',
                        'processing' => 'Sedang Diproses',
                        'done'       => 'Selesai',
                        'failed'     => 'Gagal',
                    ]),
                SelectFilter::make('import_mode')
                    ->label('Mode Import')
                    ->options([
                        'per_desa'  => 'Per Desa',
                        'kabupaten' => 'Seluruh Kabupaten',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada import PMKS')
            ->emptyStateDescription('Klik tombol "Import CSV" untuk mengimport data PMKS dari file CSV.');
    }
}
