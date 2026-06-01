<?php

namespace App\Filament\Resources\ApiClients;

use App\Enums\UserRole;
use App\Filament\Resources\ApiClients\Pages\CreateApiClient;
use App\Filament\Resources\ApiClients\Pages\ListApiClients;
use App\Filament\Resources\ApiClients\Pages\ViewApiClient;
use App\Models\ApiClient;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApiClientResource extends Resource
{
    protected static ?string $model = ApiClient::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'Token API';

    protected static ?string $modelLabel = 'Token API';

    protected static ?string $pluralModelLabel = 'Token API';

    protected static ?int $navigationSort = 99;

    public static function getNavigationGroup(): ?string
    {
        return 'Konfigurasi';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
        ]) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Informasi Instansi')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('nama_instansi')
                        ->label('Nama Instansi')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('Contoh: Dinas Komunikasi dan Informatika Buleleng'),

                    \Filament\Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan')
                        ->rows(3)
                        ->maxLength(500)
                        ->placeholder('Tujuan penggunaan API, nama kontak, dsb.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_instansi')
                    ->label('Instansi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('token_preview')
                    ->label('Token (preview)')
                    ->formatStateUsing(fn ($state) => $state ? $state . '...' : '-')
                    ->fontFamily('mono'),

                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('last_used_at')
                    ->label('Terakhir Dipakai')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum pernah')
                    ->sortable(),

                TextColumn::make('total_requests')
                    ->label('Total Request')
                    ->getStateUsing(fn (ApiClient $record) => number_format($record->total_requests))
                    ->alignRight(),

                TextColumn::make('requests_today')
                    ->label('Hari Ini')
                    ->getStateUsing(fn (ApiClient $record) => number_format($record->requests_today))
                    ->alignRight(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\Action::make('toggle_active')
                    ->label(fn (ApiClient $record) => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn (ApiClient $record) => $record->is_active ? Heroicon::OutlinedPause : Heroicon::OutlinedPlay)
                    ->color(fn (ApiClient $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (ApiClient $record) => $record->is_active ? 'Nonaktifkan Token?' : 'Aktifkan Token?')
                    ->modalDescription(fn (ApiClient $record) => $record->is_active
                        ? 'Token ini tidak akan bisa digunakan untuk akses API.'
                        : 'Token ini akan aktif kembali dan bisa digunakan untuk akses API.')
                    ->action(function (ApiClient $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                    }),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada token API')
            ->emptyStateDescription('Buat token baru untuk instansi yang membutuhkan akses data.');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListApiClients::route('/'),
            'create' => CreateApiClient::route('/create'),
            'view'   => ViewApiClient::route('/{record}'),
        ];
    }
}
