<?php

namespace App\Filament\Resources\PsksImports\Pages;

use App\Filament\Resources\PsksImports\PsksImportResource;
use App\Jobs\Psks\PsksImportParserJob;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;

class ViewPsksImport extends ViewRecord
{
    protected static string $resource = PsksImportResource::class;

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
                        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
                        fputcsv($handle, [
                            'kode_kategori', 'nik', 'nama', 'tgl_lahir',
                            'jenis_kelamin', 'tipe_lembaga', 'nomor_registrasi', 'catatan',
                        ], ';');
                        // Contoh baris individu
                        fputcsv($handle, ['PSKS-J-01', '5171234567890001', 'I WAYAN RELAWAN', '10-05-1990', 'L', '', '', 'PSM aktif sejak 2020'], ';');
                        fputcsv($handle, ['PSKS-J-03', '5171234567890002', 'NI MADE RELAWATI', '20-03-1988', 'P', '', '', ''], ';');
                        // Contoh baris lembaga
                        fputcsv($handle, ['PSKS-L-01', '', 'Karang Taruna Bhuana Utama', '', '', 'karang_taruna', 'KT-001/2020', ''], ';');
                        fputcsv($handle, ['PSKS-L-05', '', 'PKK Desa Banyuning', '', '', 'pkk', '', 'PKK aktif'], ';');
                        fclose($handle);
                    }, 'template-import-psks.csv', [
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

                    PsksImportParserJob::dispatch($import->id)->onQueue('imports');

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
