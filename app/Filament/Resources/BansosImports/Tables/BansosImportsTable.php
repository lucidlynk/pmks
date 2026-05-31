<?php

namespace App\Filament\Resources\BansosImports\Tables;

use App\Models\BansosImport;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BansosImportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jenis_label')
                    ->label('Jenis')
                    ->getStateUsing(fn (BansosImport $record) => $record->jenis_label)
                    ->badge()
                    ->color(fn (BansosImport $record) => $record->jenis_bansos === 'pkh' ? 'info' : 'success'),

                TextColumn::make('status_bansos_label')
                    ->label('Status Bansos')
                    ->getStateUsing(fn (BansosImport $record) => $record->status_bansos_label)
                    ->badge()
                    ->color(fn (BansosImport $record) => match ($record->status_bansos) {
                        'sudah_si'        => 'gray',
                        'sudah_salur'     => 'warning',
                        'sudah_transaksi' => 'success',
                        default           => 'gray',
                    }),

                TextColumn::make('triwulan_label')
                    ->label('Periode')
                    ->getStateUsing(fn (BansosImport $record) => $record->triwulan_label),

                TextColumn::make('original_filename')
                    ->label('File')
                    ->limit(35)
                    ->tooltip(fn ($record) => $record->original_filename),

                TextColumn::make('status_label')
                    ->label('Status Import')
                    ->getStateUsing(fn (BansosImport $record) => $record->status_label)
                    ->badge()
                    ->color(fn (BansosImport $record) => $record->status_color),

                TextColumn::make('total_rows')
                    ->label('Total')
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
            ])
            ->filters([
                SelectFilter::make('jenis_bansos')
                    ->label('Jenis')
                    ->options(BansosImport::jenisOptions()),

                SelectFilter::make('status_bansos')
                    ->label('Status Bansos')
                    ->options(BansosImport::statusBansosOptions()),

                SelectFilter::make('triwulan')
                    ->label('Triwulan')
                    ->options(BansosImport::triwulanOptions()),

                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(function () {
                        return BansosImport::query()
                            ->distinct()
                            ->orderByDesc('tahun')
                            ->pluck('tahun', 'tahun')
                            ->toArray();
                    }),

                SelectFilter::make('status')
                    ->label('Status Import')
                    ->options([
                        'pending'    => 'Menunggu',
                        'processing' => 'Sedang Diproses',
                        'done'       => 'Selesai',
                        'failed'     => 'Gagal',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada import Bansos')
            ->emptyStateDescription('Klik tombol "Upload CSV" untuk mengimport data Bansos.');
    }
}
