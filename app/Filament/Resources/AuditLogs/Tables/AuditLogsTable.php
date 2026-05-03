<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state) => match (true) {
                        str_contains($state, 'create') => 'success',
                        str_contains($state, 'update') => 'warning',
                        str_contains($state, 'delete') => 'danger',
                        str_contains($state, 'login')  => 'info',
                        default                         => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('model_type')
                    ->label('Model')
                    ->formatStateUsing(fn (?string $state) => $state
                        ? class_basename($state)
                        : '-')
                    ->searchable(),

                TextColumn::make('model_id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('old_values')
                    ->label('Data Lama')
                    ->formatStateUsing(fn ($state) => $state
                        ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        : '-')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('new_values')
                    ->label('Data Baru')
                    ->formatStateUsing(fn ($state) => $state
                        ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        : '-')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Aksi')
                    ->options([
                        'login'         => 'Login',
                        'logout'        => 'Logout',
                        'login_failed'  => 'Login Gagal',
                        'create'        => 'Create',
                        'update'        => 'Update',
                        'delete'        => 'Delete',
                        'reset_password'=> 'Reset Password',
                        'verify'        => 'Verifikasi',
                    ]),

                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100]);
    }
}
