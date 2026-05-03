<?php

namespace App\Filament\Resources\SubmissionBatches\Pages;

use App\Enums\BatchStatus;
use App\Exports\BatchRekapExport;
use App\Filament\Resources\SubmissionBatches\SubmissionBatchResource;
use App\Services\AuditLogService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Maatwebsite\Excel\Facades\Excel;

class ViewSubmissionBatch extends ViewRecord
{
    protected static string $resource = SubmissionBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => $this->record->canBeEdited()),

            // Tombol Export Rekap
            Action::make('export_rekap')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $batch    = $this->record;
                    $filename = "rekap-{$batch->village->name}-{$batch->period_year}.xlsx";
                    return Excel::download(new BatchRekapExport($batch->id), $filename);
                }),

            // Tombol Submit
            Action::make('submit')
                ->label('Ajukan ke Verifikator')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Ajukan Pengajuan')
                ->modalDescription('Data tidak bisa diubah setelah diajukan. Lanjutkan?')
                ->visible(fn () => $this->record->status->canSubmit())
                ->action(function () {
                    $old = $this->record->toArray();
                    $this->record->update(['status' => BatchStatus::SUBMITTED]);
                    AuditLogService::logUpdate($this->record, $old);
                    Notification::make()->title('Berhasil diajukan')->success()->send();
                    $this->refreshFormData(['status']);
                }),

            // Tombol Verifikasi
            Action::make('verify')
                ->label('Verifikasi')
                ->icon('heroicon-o-check-badge')
                ->color('warning')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('verification_notes')
                        ->label('Catatan Verifikasi')
                        ->nullable(),
                ])
                ->visible(fn () => $this->record->status->canVerify())
                ->action(function (array $data) {
                    $old = $this->record->toArray();
                    $this->record->update([
                        'status'             => BatchStatus::VERIFIED,
                        'verified_by'        => auth()->id(),
                        'verified_at'        => now(),
                        'verification_notes' => $data['verification_notes'] ?? null,
                    ]);
                    AuditLogService::logUpdate($this->record, $old);
                    Notification::make()->title('Data terverifikasi')->success()->send();
                    $this->refreshFormData(['status']);
                }),

            // Tombol Approve
            Action::make('approve')
                ->label('Setujui')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Setujui Pengajuan')
                ->modalDescription('Data akan dikunci setelah disetujui.')
                ->visible(fn () => $this->record->status->canApprove())
                ->action(function () {
                    $old = $this->record->toArray();
                    $this->record->update([
                        'status'      => BatchStatus::APPROVED,
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);
                    AuditLogService::logUpdate($this->record, $old);
                    Notification::make()->title('Pengajuan disetujui')->success()->send();
                    $this->refreshFormData(['status']);
                }),

            // Tombol Tolak
            Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_notes')
                        ->label('Alasan Penolakan')
                        ->required(),
                ])
                ->visible(fn () => $this->record->status->canReject())
                ->action(function (array $data) {
                    $old = $this->record->toArray();
                    $this->record->update([
                        'status'          => BatchStatus::REJECTED,
                        'rejection_notes' => $data['rejection_notes'],
                    ]);
                    AuditLogService::logUpdate($this->record, $old);
                    Notification::make()->title('Pengajuan ditolak')->warning()->send();
                    $this->refreshFormData(['status']);
                }),

            // Tombol Minta Revisi
            Action::make('request_revision')
                ->label('Minta Revisi')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Alasan Revisi')
                        ->required(),
                ])
                ->visible(fn () => $this->record->status->canRequestRevision())
                ->action(function (array $data) {
                    $old = $this->record->toArray();
                    $this->record->update(['status' => BatchStatus::REVISION_REQUESTED]);
                    $this->record->revisions()->create([
                        'requested_by' => auth()->id(),
                        'reason'       => $data['reason'],
                        'status'       => 'pending',
                    ]);
                    AuditLogService::logUpdate($this->record, $old);
                    Notification::make()->title('Permintaan revisi dikirim')->warning()->send();
                    $this->refreshFormData(['status']);
                }),
        ];
    }
}
