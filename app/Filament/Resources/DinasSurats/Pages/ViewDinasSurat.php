<?php

namespace App\Filament\Resources\DinasSurats\Pages;

use App\Filament\Resources\DinasSurats\DinasSuratResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class ViewDinasSurat extends ViewRecord
{
    protected static string $resource = DinasSuratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Download Surat')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => $this->record->is_active)
                ->action(function () {
                    $path = $this->record->file_path;

                    if (!Storage::disk('local')->exists($path)) {
                        $this->notify('danger', 'File tidak ditemukan.');
                        return;
                    }

                    return Storage::disk('local')->download(
                        $path,
                        $this->record->original_filename
                    );
                }),

            EditAction::make()
                ->visible(fn () => auth()->user()?->hasRole('admin_dinsos')),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Informasi Surat')
                ->columns(2)
                ->schema([
                    TextEntry::make('judul')
                        ->label('Judul Surat')
                        ->columnSpanFull(),

                    TextEntry::make('nomor_surat')
                        ->label('Nomor Surat')
                        ->placeholder('-'),

                    TextEntry::make('tanggal_surat')
                        ->label('Tanggal Surat')
                        ->date('d M Y'),

                    TextEntry::make('kategori_label')
                        ->label('Kategori')
                        ->getStateUsing(fn ($record) => $record->kategori_label)
                        ->badge()
                        ->color(fn ($record) => $record->kategori_color),

                    TextEntry::make('target_scope')
                        ->label('Target')
                        ->formatStateUsing(fn ($state) => $state === 'semua' ? 'Semua Desa/Kelurahan' : 'Kecamatan Tertentu')
                        ->badge()
                        ->color(fn ($state) => $state === 'semua' ? 'success' : 'warning'),

                    TextEntry::make('deskripsi')
                        ->label('Deskripsi')
                        ->placeholder('-')
                        ->columnSpanFull(),
                ]),

            Section::make('File')
                ->columns(3)
                ->schema([
                    TextEntry::make('original_filename')
                        ->label('Nama File'),

                    TextEntry::make('file_size_label')
                        ->label('Ukuran File')
                        ->getStateUsing(fn ($record) => $record->file_size_label),

                    TextEntry::make('is_active')
                        ->label('Status')
                        ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Nonaktif')
                        ->badge()
                        ->color(fn ($state) => $state ? 'success' : 'danger'),
                ]),

            Section::make('Informasi Upload')
                ->columns(2)
                ->schema([
                    TextEntry::make('uploadedBy.name')
                        ->label('Diupload Oleh'),

                    TextEntry::make('created_at')
                        ->label('Tanggal Upload')
                        ->dateTime('d M Y H:i'),
                ]),

        ]);
    }
}
