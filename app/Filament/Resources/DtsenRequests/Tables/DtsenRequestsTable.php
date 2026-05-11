<?php

namespace App\Filament\Resources\DtsenRequests\Tables;

use App\Enums\DtsenStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DtsenRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('No. Referensi')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('village.name')
                    ->label('Desa')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('residents.name')
                    ->label('Nama Warga')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList(),

                TextColumn::make('residents.nik')
                    ->label('NIK')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Diajukan Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->purpose),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (DtsenStatus $state) => $state->label())
                    ->color(fn (DtsenStatus $state) => $state->color()),

                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(DtsenStatus::options()),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
