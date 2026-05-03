<?php

namespace App\Filament\Resources\Institutions\Tables;

use App\Models\Kecamatan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class InstitutionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lembaga')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'karang_taruna'  => 'success',
                        'pkk'            => 'info',
                        'lks'            => 'warning',
                        'lksa'           => 'danger',
                        default          => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'karang_taruna'  => 'Karang Taruna',
                        'pkk'            => 'PKK',
                        'lks'            => 'LKS',
                        'lksa'           => 'LKSA',
                        'lembaga_sosial' => 'Lembaga Sosial',
                        'orsos'          => 'Orsos',
                        'other'          => 'Lainnya',
                        default          => $state,
                    }),

                TextColumn::make('village.name')
                    ->label('Desa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('village.kecamatan.name')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contact_person')
                    ->label('Kontak')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('phone')
                    ->label('Telepon')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis Lembaga')
                    ->options([
                        'karang_taruna'  => 'Karang Taruna',
                        'pkk'            => 'PKK',
                        'lks'            => 'LKS',
                        'lksa'           => 'LKSA',
                        'lembaga_sosial' => 'Lembaga Sosial',
                        'orsos'          => 'Orsos',
                        'other'          => 'Lainnya',
                    ]),

                SelectFilter::make('village_id')
                    ->label('Desa')
                    ->relationship('village', 'name'),

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
            ->defaultSort('name');
    }
}
