<?php

namespace App\Filament\Resources\ApiClients\Pages;

use App\Filament\Resources\ApiClients\ApiClientResource;
use App\Models\ApiClient;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;

class ViewApiClient extends ViewRecord
{
    protected static string $resource = ApiClientResource::class;

    protected static ?string $title = 'Detail Token API';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (session()->has('new_api_token')) {
            $token    = session()->pull('new_api_token');
            $instansi = session()->pull('new_api_token_instansi', 'instansi');

            Notification::make()
                ->title("Token untuk {$instansi} — Simpan sekarang!")
                ->body("Token ini hanya ditampilkan sekali:\n\n{$token}")
                ->success()
                ->persistent()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggle_active')
                ->label(fn () => $this->record->is_active ? 'Nonaktifkan Token' : 'Aktifkan Token')
                ->icon(fn () => $this->record->is_active ? Heroicon::OutlinedPause : Heroicon::OutlinedPlay)
                ->color(fn () => $this->record->is_active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->modalHeading(fn () => $this->record->is_active ? 'Nonaktifkan Token?' : 'Aktifkan Token?')
                ->modalDescription(fn () => $this->record->is_active
                    ? 'Token ini tidak akan bisa digunakan untuk akses API.'
                    : 'Token ini akan aktif kembali.')
                ->action(function (): void {
                    $this->record->update(['is_active' => ! $this->record->is_active]);
                    Notification::make()
                        ->title($this->record->fresh()->is_active ? 'Token diaktifkan' : 'Token dinonaktifkan')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Token')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('nama_instansi')->label('Nama Instansi'),

                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->getStateUsing(fn (ApiClient $record) => $record->is_active ? 'Aktif' : 'Nonaktif')
                            ->color(fn (string $state) => $state === 'Aktif' ? 'success' : 'danger'),

                        TextEntry::make('token_preview')
                            ->label('Token (8 karakter pertama)')
                            ->formatStateUsing(fn ($state) => $state ? $state . '...' : '-')
                            ->fontFamily('mono'),

                        TextEntry::make('last_used_at')
                            ->label('Terakhir Dipakai')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Belum pernah'),

                        TextEntry::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
                ]),

            Section::make('Statistik Penggunaan')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('total_requests')
                            ->label('Total Request')
                            ->getStateUsing(fn (ApiClient $record) => number_format($record->total_requests)),

                        TextEntry::make('requests_today')
                            ->label('Request Hari Ini')
                            ->getStateUsing(fn (ApiClient $record) => number_format($record->requests_today)),

                        TextEntry::make('created_at')
                            ->label('Token Dibuat')
                            ->dateTime('d M Y H:i'),
                    ]),
                ]),

            Section::make('Log Akses Terbaru (50 terakhir)')
                ->schema([
                    RepeatableEntry::make('recent_logs')
                        ->label('')
                        ->getStateUsing(fn (ApiClient $record) => $record->logs()
                            ->orderByDesc('accessed_at')
                            ->limit(50)
                            ->get()
                            ->toArray()
                        )
                        ->schema([
                            TextEntry::make('accessed_at')
                                ->label('Waktu')
                                ->dateTime('d M Y H:i:s'),

                            TextEntry::make('endpoint')
                                ->label('Endpoint')
                                ->fontFamily('mono'),

                            TextEntry::make('response_code')
                                ->label('HTTP')
                                ->badge()
                                ->color(fn ($state) => match (true) {
                                    $state >= 200 && $state < 300 => 'success',
                                    $state >= 400 && $state < 500 => 'warning',
                                    default                       => 'danger',
                                }),

                            TextEntry::make('ip_address')->label('IP'),
                        ])
                        ->columns(4),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }
}
