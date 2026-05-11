<?php

namespace App\Filament\Resources\DtsenRequests\Pages;

use App\Enums\DtsenStatus;
use App\Enums\UserRole;
use App\Filament\Resources\DtsenRequests\DtsenRequestResource;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ViewDtsenRequest extends ViewRecord
{
    protected static string $resource = DtsenRequestResource::class;

    // Filament v4: override form() untuk tampilan view-only
    // infolist() tidak dipakai jika Resource punya form()
    // Solusi: gunakan form() dengan disabled components

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Permohonan')
                ->columns(2)
                ->schema([
                    TextEntry::make('reference_number')
                        ->label('No. Referensi')
                        ->copyable(),

                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn (DtsenStatus $state) => $state->label())
                        ->color(fn (DtsenStatus $state) => $state->color()),

                    TextEntry::make('village.name')
                        ->label('Desa'),

                    TextEntry::make('user.name')
                        ->label('Diajukan Oleh'),

                    TextEntry::make('purpose')
                        ->label('Keperluan')
                        ->columnSpanFull(),

                    TextEntry::make('notes')
                        ->label('Catatan')
                        ->columnSpanFull()
                        ->placeholder('Tidak ada catatan'),
                ]),

            Section::make('Data Warga')
                ->schema([
                    RepeatableEntry::make('residents')
                        ->label('')
                        ->schema([
                            TextEntry::make('nik')
                                ->label('NIK'),
                            TextEntry::make('name')
                                ->label('Nama'),
                            TextEntry::make('birth_place')
                                ->label('Tempat Lahir'),
                            TextEntry::make('birth_date')
                                ->label('Tanggal Lahir')
                                ->date('d M Y'),
                            TextEntry::make('gender')
                                ->label('Jenis Kelamin')
                                ->formatStateUsing(
                                    fn ($state) => $state === 'L' ? 'Laki-laki' : 'Perempuan'
                                ),
                        ])
                        ->columns(5),
                ]),

            Section::make('Informasi Proses')
                ->columns(2)
                ->visible(fn () => $this->record->processed_by !== null)
                ->schema([
                    TextEntry::make('processedBy.name')
                        ->label('Diproses Oleh'),

                    TextEntry::make('processed_at')
                        ->label('Tanggal Proses')
                        ->dateTime('d M Y H:i'),
                ]),

            Section::make('Dokumen Surat')
                ->visible(fn () => $this->record->currentDocument !== null)
                ->schema([
                    TextEntry::make('currentDocument.original_filename')
                        ->label('Nama File'),

                    TextEntry::make('currentDocument.file_size')
                        ->label('Ukuran File')
                        ->formatStateUsing(
                            fn () => $this->record->currentDocument?->getFileSizeForHumans()
                        ),

                    TextEntry::make('currentDocument.created_at')
                        ->label('Diupload Pada')
                        ->dateTime('d M Y H:i'),

                    TextEntry::make('currentDocument.uploadedBy.name')
                        ->label('Diupload Oleh'),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->submitAction(),
            $this->processAction(),
            $this->uploadPdfAction(),
            $this->downloadPdfAction(),
            $this->cancelAction(),
        ];
    }

    private function submitAction(): Action
    {
        return Action::make('submit')
            ->label('Ajukan Permohonan')
            ->icon('heroicon-o-paper-airplane')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Ajukan Permohonan?')
            ->modalDescription('Permohonan akan dikirim ke Dinsos untuk diproses.')
            ->visible(
                fn () => $this->record->status->canSubmit()
                    && $this->record->isOwnedBy(auth()->user())
            )
            ->action(function (): void {
                $this->record->update(['status' => DtsenStatus::SUBMITTED->value]);
                Notification::make()
                    ->title('Permohonan berhasil diajukan')
                    ->success()
                    ->send();
                $this->refreshFormData(['status']);
            });
    }

    private function processAction(): Action
    {
        return Action::make('process')
            ->label('Proses Permohonan')
            ->icon('heroicon-o-cog-6-tooth')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Proses Permohonan?')
            ->modalDescription('Status akan berubah menjadi Sedang Diproses.')
            ->visible(
                fn () => $this->record->status->canProcess()
                    && auth()->user()?->hasAnyRole([
                        UserRole::ADMIN_DINSOS->value,
                        UserRole::VERIFIKATOR->value,
                    ])
            )
            ->action(function (): void {
                $this->record->update([
                    'status'       => DtsenStatus::ON_PROCESS->value,
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                ]);
                Notification::make()
                    ->title('Permohonan sedang diproses')
                    ->warning()
                    ->send();
                $this->refreshFormData(['status']);
            });
    }

    private function uploadPdfAction(): Action
    {
        return Action::make('uploadPdf')
            ->label('Upload Surat PDF')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->visible(
                fn () => $this->record->status->canUploadPdf()
                    && auth()->user()?->hasAnyRole([
                        UserRole::ADMIN_DINSOS->value,
                        UserRole::VERIFIKATOR->value,
                    ])
            )
            ->form([
                FileUpload::make('pdf_file')
                    ->label('File Surat DTSEN (PDF)')
                    ->required()
                    ->disk('private')
                    ->directory('dtsen')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(5120)
                    ->helperText('Maksimal 5MB, format PDF'),
            ])
            ->action(function (array $data): void {
                DB::transaction(function () use ($data): void {
                    $this->record->documents()
                        ->where('is_current', true)
                        ->update(['is_current' => false]);
                    $path = $data['pdf_file'];
                    $this->record->documents()->create([
                        'file_path'         => $path,
                        'original_filename' => basename($path),
                        'file_size'         => Storage::disk('private')->size($path),
                        'is_current'        => true,
                        'uploaded_by'       => auth()->id(),
                    ]);
                    $this->record->update(['status' => DtsenStatus::READY->value]);
                });
                Notification::make()
                    ->title('Surat berhasil diupload')
                    ->success()
                    ->send();
                $this->refreshFormData(['status']);
            });
    }

    private function downloadPdfAction(): Action
    {
        return Action::make('downloadPdf')
            ->label('Download Surat')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->visible(
                fn () => $this->record->status->canDownloadPdf()
                    && $this->record->currentDocument !== null
            )
            ->action(function (): mixed {
                $document = $this->record->currentDocument;
                if (! $document || ! $document->existsOnDisk()) {
                    Notification::make()
                        ->title('File tidak ditemukan di server')
                        ->danger()
                        ->send();
                    return null;
                }
                return Storage::disk('private')->download(
                    $document->file_path,
                    str_replace('/', '-', 'DTSEN-' . $this->record->reference_number . '.pdf')
                );
            });
    }

    private function cancelAction(): Action
    {
        return Action::make('cancel')
            ->label('Batalkan')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Batalkan Permohonan?')
            ->modalDescription('Permohonan yang dibatalkan tidak dapat diajukan kembali.')
            ->visible(
                fn () => $this->record->status->canCancel()
                    && $this->record->isOwnedBy(auth()->user())
            )
            ->action(function (): void {
                $this->record->update(['status' => DtsenStatus::CANCELLED->value]);
                Notification::make()
                    ->title('Permohonan dibatalkan')
                    ->danger()
                    ->send();
                $this->refreshFormData(['status']);
            });
    }
}
