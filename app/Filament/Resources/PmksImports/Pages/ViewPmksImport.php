<?php

namespace App\Filament\Resources\PmksImports\Pages;

use App\Filament\Resources\PmksImports\PmksImportResource;
use App\Jobs\Pmks\PmksImportParserJob;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;

class ViewPmksImport extends ViewRecord
{
    protected static string $resource = PmksImportResource::class;

    protected function getRefreshInterval(): ?string
    {
        return $this->record->isFinished() ? null : '4s';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    return response()->streamDownload(function () {
                        $handle = fopen('php://output', 'w');
                        // BOM UTF-8 agar Excel baca dengan benar
                        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
                        fputcsv($handle, ['nik', 'nama', 'tgl_lahir', 'jenis_kelamin', 'kode_kategori', 'catatan', 'jenis_disabilitas'], ';');
                        // Contoh data
                        fputcsv($handle, ['5171234567890001', 'I WAYAN CONTOH', '15-08-1985', 'L', 'PMKS-24', 'Fakir miskin wilayah pesisir', ''], ';');
                        fputcsv($handle, ['5171234567890002', 'NI MADE CONTOH', '20-03-1990', 'P', 'PMKS-23', '', ''], ';');
                        fputcsv($handle, ['5171234567890003', 'I MADE DISABILITAS', '10-05-2008', 'L', 'PMKS-05', '', 'fisik|mental'], ';');
                        fclose($handle);
                    }, 'template-import-pmks.csv', [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),

            Action::make('retryImport')
                ->label('Proses Ulang')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Proses Ulang Import?')
                ->modalDescription('Submission yang sudah berhasil diimpor TIDAK akan dihapus. Hanya baris yang belum masuk yang akan diproses ulang.')
                ->modalSubmitActionLabel('Ya, Proses Ulang')
                ->visible(function () {
                    $record = $this->record;
                    if ($record->isFailed()) return true;
                    if ($record->isProcessing() && $record->started_at) {
                        return $record->started_at->diffInMinutes(now()) > 30;
                    }
                    return false;
                })
                ->action(function () {
                    $import = $this->record;

                    if (!Storage::disk('local')->exists($import->file_path)) {
                        Notification::make()
                            ->danger()
                            ->title('File tidak ditemukan')
                            ->body('File CSV original sudah tidak ada. Silakan upload ulang.')
                            ->send();
                        return;
                    }

                    $import->update([
                        'status'        => 'pending',
                        'job_batch_id'  => null,
                        'total_rows'    => null,
                        'success_rows'  => 0,
                        'failed_rows'   => 0,
                        'error_summary' => null,
                        'started_at'    => null,
                        'finished_at'   => null,
                    ]);

                    PmksImportParserJob::dispatch($import->id)->onQueue('imports');

                    Notification::make()
                        ->success()
                        ->title('Import dijadwalkan ulang')
                        ->body('Proses berjalan di background.')
                        ->send();
                }),

            DeleteAction::make()
                ->visible(fn () => $this->record->isFinished()),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Informasi Batch')
                ->columns(3)
                ->schema([
                    TextEntry::make('submissionBatch.village.kecamatan.name')
                        ->label('Kecamatan'),

                    TextEntry::make('submissionBatch.village.name')
                        ->label('Desa / Kelurahan'),

                    TextEntry::make('submissionBatch.period_year')
                        ->label('Tahun Periode')
                        ->weight(FontWeight::Bold),
                ]),

            Section::make('Status Import')
                ->columns(3)
                ->schema([
                    TextEntry::make('status_label')
                        ->label('Status')
                        ->getStateUsing(fn ($record) => $record->status_label)
                        ->badge()
                        ->color(fn ($record) => $record->status_color),

                    TextEntry::make('progress')
                        ->label('Progress')
                        ->getStateUsing(fn ($record) => $record->progress . '%'),

                    TextEntry::make('total_rows')
                        ->label('Total Baris')
                        ->numeric(thousandsSeparator: '.')
                        ->placeholder('-'),
                ]),

            Section::make('Statistik')
                ->columns(2)
                ->schema([
                    TextEntry::make('success_rows')
                        ->label('Berhasil Diimpor')
                        ->numeric(thousandsSeparator: '.')
                        ->color('success'),

                    TextEntry::make('failed_rows')
                        ->label('Gagal / Dilewati')
                        ->numeric(thousandsSeparator: '.')
                        ->color('danger'),
                ]),

            Section::make('Informasi File')
                ->columns(2)
                ->schema([
                    TextEntry::make('original_filename')
                        ->label('Nama File'),

                    TextEntry::make('createdBy.name')
                        ->label('Diupload Oleh'),

                    TextEntry::make('created_at')
                        ->label('Waktu Upload')
                        ->dateTime('d M Y H:i'),

                    TextEntry::make('finished_at')
                        ->label('Selesai Diproses')
                        ->dateTime('d M Y H:i')
                        ->placeholder('Belum selesai'),
                ]),

            Section::make('Detail Error')
                ->visible(fn ($record) => !empty($record->error_summary))
                ->schema([
                    TextEntry::make('error_summary')
                        ->label('Baris yang Gagal / Dilewati')
                        ->getStateUsing(fn ($record) => implode("\n", $record->error_summary ?? []))
                        ->columnSpanFull(),
                ]),

        ]);
    }
}
