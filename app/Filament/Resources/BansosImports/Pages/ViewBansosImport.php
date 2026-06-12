<?php

namespace App\Filament\Resources\BansosImports\Pages;

use App\Filament\Resources\BansosImports\BansosImportResource;
use App\Jobs\Bansos\BansosParserJob;
use App\Models\BansosMember;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;

class ViewBansosImport extends ViewRecord
{
    protected static string $resource = BansosImportResource::class;

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
                ->modalDescription('Data lama untuk periode & status ini akan dihapus dan diproses ulang dari file yang sama.')
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
                        'status'         => 'pending',
                        'batch_id'       => null,
                        'total_rows'     => null,
                        'processed_rows' => 0,
                        'failed_rows'    => 0,
                        'error_summary'  => null,
                        'started_at'     => null,
                        'finished_at'    => null,
                    ]);

                    BansosParserJob::dispatch($import->id)->onQueue('imports');

                    Notification::make()
                        ->success()
                        ->title('Import dijadwalkan ulang')
                        ->body('Proses berjalan di background.')
                        ->send();
                }),

            Action::make('downloadCsv')
                ->label('Download CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => auth()->user()?->can('download', $this->record))
                ->action(function () {
                    $import = $this->record;

                    return response()->streamDownload(function () use ($import) {
                        $handle = fopen('php://output', 'w');
                        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
                        fputcsv($handle, ['NAMA_PENERIMA', 'NIK', 'NOKK', 'PENYALURAN_OLEH', 'JENIS_BANSOS', 'KEC_NAME', 'KEL_NAME', 'STATUS_BANSOS', 'KODE_BATCH', 'TRIWULAN', 'TAHUN']);

                        BansosMember::where('import_id', $import->id)
                            ->orderBy('id')
                            ->chunk(1000, function ($rows) use ($handle) {
                                foreach ($rows as $row) {
                                    fputcsv($handle, [
                                        $row->nama_penerima,
                                        $row->nik,
                                        $row->nokk,
                                        $row->penyaluran_oleh,
                                        $row->jenis_bansos,
                                        $row->kec_name,
                                        $row->kel_name,
                                        $row->status_bansos,
                                        $row->kode_batch,
                                        $row->triwulan,
                                        $row->tahun,
                                    ]);
                                }
                            });

                        fclose($handle);
                    }, "BANSOS-{$import->jenis_bansos}-TW{$import->triwulan}-{$import->tahun}-{$import->status_bansos}.csv", [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),

            DeleteAction::make()
                ->visible(fn () => $this->record->isFinished()),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Informasi Import')
                ->columns(3)
                ->schema([
                    TextEntry::make('jenis_label')
                        ->label('Jenis Bansos')
                        ->getStateUsing(fn ($record) => $record->jenis_label)
                        ->badge()
                        ->color(fn ($record) => $record->jenis_bansos === 'pkh' ? 'info' : 'success'),

                    TextEntry::make('status_bansos_label')
                        ->label('Status Bansos')
                        ->getStateUsing(fn ($record) => $record->status_bansos_label)
                        ->badge()
                        ->color(fn ($record) => match ($record->status_bansos) {
                            'sudah_si'        => 'gray',
                            'sudah_salur'     => 'warning',
                            'sudah_transaksi' => 'success',
                            default           => 'gray',
                        }),

                    TextEntry::make('triwulan_label')
                        ->label('Periode')
                        ->getStateUsing(fn ($record) => $record->triwulan_label)
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
