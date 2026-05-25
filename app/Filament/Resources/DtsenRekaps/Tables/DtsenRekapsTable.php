<?php

namespace App\Filament\Resources\DtsenRekaps\Tables;

use App\Enums\UserRole;
use App\Models\DtsenRekap;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DtsenRekapsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bulan')
                    ->label('Bulan')
                    ->formatStateUsing(
                        fn (int $state) => DtsenRekap::bulanOptions()[$state] ?? '-'
                    )
                    ->sortable(),

                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable(),

                TextColumn::make('details_count')
                    ->label('Jml Desa/Kel')
                    ->counts('details')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('original_filename')
                    ->label('Nama File')
                    ->searchable()
                    ->limit(35)
                    ->tooltip(fn ($state) => $state),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(40)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('uploader.name')
                    ->label('Diupload Oleh')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tanggal Upload')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('bulan')
                    ->label('Bulan')
                    ->options(DtsenRekap::bulanOptions()),

                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(function () {
                        $currentYear = (int) now()->year;
                        $years       = range($currentYear, 2020);
                        return array_combine($years, $years);
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Detail'),

                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (DtsenRekap $record) {
                        return Storage::disk('local')->download(
                            $record->file_path,
                            $record->original_filename
                        );
                    }),

                ForceDeleteAction::make()
                    ->visible(
                        fn () => auth()->user()?->hasRole(UserRole::ADMIN_DINSOS->value)
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ForceDeleteBulkAction::make()
                        ->visible(
                            fn () => auth()->user()?->hasRole(UserRole::ADMIN_DINSOS->value)
                        ),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
