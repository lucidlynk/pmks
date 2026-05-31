<?php

namespace App\Filament\Resources\KisPbiApbdImports\Pages;

use App\Filament\Resources\KisPbiApbdImports\KisPbiApbdImportResource;
use App\Jobs\Kis\KisPbiApbdParserJob;
use App\Models\KisPbiApbdImport;
use App\Models\KisPbiApbdMember;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

class ViewKisPbiApbdImport extends ViewRecord
{
    protected static string $resource = KisPbiApbdImportResource::class;

    protected function getRefreshInterval(): ?string
    {
        return $this->record->isFinished() ? null : '4s';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retryImport')
                ->label('Proses Ulang')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Proses Ulang Import?')
                ->modalDescription('Data yang sudah diimport untuk periode ini akan ditimpa ulang. Proses akan berjalan di background.')
                ->modalSubmitActionLabel('Ya, Proses Ulang')
                ->visible(function () {
                    $record = $this->record;

                    // Tampil kalau: failed, atau processing lebih dari 30 menit
                    if ($record->isFailed()) return true;

                    if ($record->isProcessing() && $record->started_at) {
                        return $record->started_at->diffInMinutes(now()) > 30;
                    }

                    return false;
                })
                ->action(function () {
                    $import = $this->record;

                    // Pastikan file masih ada
                    if (!Storage::disk('local')->exists($import->file_path)) {
                        Notification::make()
                            ->danger()
                            ->title('File tidak ditemukan')
                            ->body('File CSV original sudah tidak ada di storage. Silakan upload ulang.')
                            ->send();
                        return;
                    }

                    // Reset status import
                    $import->update([
                        'status'         => 'pending',
                        'batch_id'       => null,
                        'total_rows'     => null,
                        'processed_rows' => 0,
                        'failed_rows'    => 0,
                        'error_summary'  => null,
                        'started_at'     => null,
                        'finished_at'    => null,
                    ]);

                    // Dispatch parser job ulang
                    KisPbiApbdParserJob::dispatch($import->id)
                        ->onQueue('imports');

                    Notification::make()
                        ->success()
                        ->title('Import dijadwalkan ulang')
                        ->body('Proses import berjalan di background. Halaman ini akan update otomatis.')
                        ->send();
                }),

            Action::make('downloadCsv')
                ->label('Download CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => auth()->user()?->hasRole('admin_dinsos') && $this->record->isDone())
                ->action(function () {
                    $import = $this->record;

                    return response()->streamDownload(function () use ($import) {
                        $handle = fopen('php://output', 'w');
                        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
                        fputcsv($handle, ['PSNOKA', 'NIK', 'NAMA', 'SEGMEN', 'BULAN', 'TAHUN']);

                        KisPbiApbdMember::where('import_id', $import->id)
                            ->orderBy('id')
                            ->chunk(1000, function ($rows) use ($handle) {
                                foreach ($rows as $row) {
                                    fputcsv($handle, [
                                        $row->psnoka,
                                        $row->nik,
                                        $row->nama,
                                        $row->segmen,
                                        $row->periode_bulan,
                                        $row->periode_tahun,
                                    ]);
                                }
                            });

                        fclose($handle);
                    }, "PBI-APBD-{$import->periode_tahun}-{$import->periode_bulan}.csv", [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),

            DeleteAction::make()
                ->visible(fn () => $this->record->isFinished()),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        $record   = $this->record;
        $progress = $record->progress;

        return $schema->components([

            Section::make('Status Import')
                ->columns(3)
                ->schema([
                    TextEntry::make('periode_label')
                        ->label('Periode')
                        ->getStateUsing(fn ($record) => $record->periode_label)
                        ->weight(FontWeight::Bold),

                    TextEntry::make('status_label')
                        ->label('Status')
                        ->getStateUsing(fn ($record) => $record->status_label)
                        ->badge()
                        ->color(fn ($record) => $record->status_color),

                    TextEntry::make('progress_label')
                        ->label('Progress')
                        ->getStateUsing(fn ($record) => $record->progress . '%'),
                ]),

            Section::make('Statistik')
                ->columns(3)
                ->schema([
                    TextEntry::make('total_rows')
                        ->label('Total Baris')
                        ->numeric(thousandsSeparator: '.')
                        ->placeholder('-'),

                    TextEntry::make('processed_rows')
                        ->label('Berhasil Diproses')
                        ->numeric(thousandsSeparator: '.')
                        ->color('success'),

                    TextEntry::make('failed_rows')
                        ->label('Gagal')
                        ->numeric(thousandsSeparator: '.')
                        ->color('danger'),
                ]),

            Section::make('Informasi File')
                ->columns(2)
                ->schema([
                    TextEntry::make('original_filename')
                        ->label('Nama File'),

                    TextEntry::make('uploadedBy.name')
                        ->label('Diupload Oleh'),

                    TextEntry::make('created_at')
                        ->label('Waktu Upload')
                        ->dateTime('d M Y H:i'),

                    TextEntry::make('finished_at')
                        ->label('Selesai Diproses')
                        ->dateTime('d M Y H:i')
                        ->placeholder('Belum selesai'),
                ]),

            Section::make('Error Detail')
                ->visible(fn ($record) => !empty($record->error_summary))
                ->schema([
                    TextEntry::make('error_summary')
                        ->label('Baris yang Gagal')
                        ->getStateUsing(fn ($record) => implode("\n", $record->error_summary ?? []))
                        ->columnSpanFull(),
                ]),

        ]);
    }
}
