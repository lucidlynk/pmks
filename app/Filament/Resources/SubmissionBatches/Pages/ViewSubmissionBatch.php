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

    private function isAdminDinsos(): bool
    {
        return auth()->user()?->hasRole('admin_dinsos') ?? false;
    }

    private function isVerifikatorOrAdmin(): bool
    {
        return auth()->user()?->hasAnyRole(['verifikator', 'admin_dinsos']) ?? false;
    }

    private function isOperatorDesa(): bool
    {
        return auth()->user()?->isOperatorDesa() ?? false;
    }

    private function sendNotifToOperator(string $title, string $body, string $color): void
    {
        $operator = $this->record->submittedBy;
        if ($operator) {
            Notification::make()
                ->title($title)
                ->body($body)
                ->color($color)
                ->sendToDatabase($operator);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => $this->record->canBeEdited()),

            Action::make('export_rekap')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $batch    = $this->record;
                    $filename = "rekap-{$batch->village->name}-{$batch->period_year}.xlsx";
                    return Excel::download(new BatchRekapExport($batch->id), $filename);
                }),

            Action::make('submit')
                ->label('Ajukan ke Verifikator')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Ajukan Pengajuan')
                ->modalDescription('Data tidak bisa diubah setelah diajukan. Lanjutkan?')
                ->visible(fn () => $this->record->status->canSubmit() && !$this->isVerifikatorOrAdmin())
                ->action(function () {
                    $old = $this->record->toArray();
                    $this->record->update(['status' => BatchStatus::SUBMITTED]);
                    AuditLogService::logUpdate($this->record, $old);
                    Notification::make()->title('Berhasil diajukan')->success()->send();
                    $this->refreshFormData(['status']);
                }),

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
                ->visible(fn () => $this->record->status->canVerify() && $this->isVerifikatorOrAdmin())
                ->action(function (array $data) {
                    $old = $this->record->toArray();
                    $this->record->update([
                        'status'             => BatchStatus::VERIFIED,
                        'verified_by'        => auth()->id(),
                        'verified_at'        => now(),
                        'verification_notes' => $data['verification_notes'] ?? null,
                    ]);
                    AuditLogService::logUpdate($this->record, $old);
                    $this->sendNotifToOperator(
                        'Pengajuan Anda Terverifikasi',
                        "Batch {$this->record->village->name} tahun {$this->record->period_year} telah diverifikasi dan diteruskan ke Admin Dinsos.",
                        'warning'
                    );
                    Notification::make()->title('Data terverifikasi')->success()->send();
                    $this->refreshFormData(['status']);
                }),

            Action::make('approve')
                ->label('Setujui')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Setujui Pengajuan')
                ->modalDescription('Data akan dikunci setelah disetujui.')
                ->visible(fn () => $this->record->status->canApprove() && $this->isAdminDinsos())
                ->action(function () {
                    $old = $this->record->toArray();
                    $this->record->update([
                        'status'      => BatchStatus::APPROVED,
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);
                    AuditLogService::logUpdate($this->record, $old);
                    $this->sendNotifToOperator(
                        'Pengajuan Anda Disetujui',
                        "Batch {$this->record->village->name} tahun {$this->record->period_year} telah disetujui. Data resmi tercatat.",
                        'success'
                    );
                    Notification::make()->title('Pengajuan disetujui')->success()->send();
                    $this->refreshFormData(['status']);
                }),

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
                ->visible(fn () => $this->record->status->canReject() && $this->isVerifikatorOrAdmin())
                ->action(function (array $data) {
                    $old = $this->record->toArray();
                    $this->record->update([
                        'status'          => BatchStatus::REJECTED,
                        'rejection_notes' => $data['rejection_notes'],
                    ]);
                    AuditLogService::logUpdate($this->record, $old);
                    $this->sendNotifToOperator(
                        'Pengajuan Anda Ditolak',
                        "Batch {$this->record->village->name} tahun {$this->record->period_year} ditolak. Alasan: {$data['rejection_notes']}",
                        'danger'
                    );
                    Notification::make()->title('Pengajuan ditolak')->warning()->send();
                    $this->refreshFormData(['status']);
                }),

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
                ->visible(fn () => $this->record->status->canRequestRevision() && $this->isAdminDinsos())
                ->action(function (array $data) {
                    $old = $this->record->toArray();
                    $this->record->update(['status' => BatchStatus::REVISION_REQUESTED]);
                    $this->record->revisions()->create([
                        'requested_by' => auth()->id(),
                        'reason'       => $data['reason'],
                        'status'       => 'pending',
                    ]);
                    AuditLogService::logUpdate($this->record, $old);
                    $this->sendNotifToOperator(
                        'Pengajuan Perlu Direvisi',
                        "Batch {$this->record->village->name} tahun {$this->record->period_year} perlu diperbaiki. Alasan: {$data['reason']}",
                        'warning'
                    );
                    Notification::make()->title('Permintaan revisi dikirim')->warning()->send();
                    $this->refreshFormData(['status']);
                }),

            Action::make('reopen')
                ->label('Perbaiki & Ajukan Ulang')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Perbaiki Pengajuan')
                ->modalDescription('Batch akan dikembalikan ke status Draft agar bisa diperbaiki dan diajukan ulang.')
                ->visible(fn () => $this->record->status === \App\Enums\BatchStatus::REJECTED && !$this->isVerifikatorOrAdmin())
                ->action(function () {
                    $old = $this->record->toArray();
                    $this->record->update(['status' => \App\Enums\BatchStatus::DRAFT]);
                    AuditLogService::logUpdate($this->record, $old);
                    Notification::make()
                        ->title('Batch dikembalikan ke Draft')
                        ->body('Silakan perbaiki data dan ajukan ulang.')
                        ->success()
                        ->send();
                    $this->refreshFormData(['status']);
                }),

            // Terima Revisi -- hanya Operator Desa
            Action::make('accept_revision')
                ->label('Terima & Mulai Perbaikan')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Terima Permintaan Revisi')
                ->modalDescription('Batch akan dibuka untuk diperbaiki. Setelah selesai, ajukan ulang ke Verifikator.')
                ->visible(fn () => $this->record->status === \App\Enums\BatchStatus::REVISION_REQUESTED && $this->isOperatorDesa())
                ->action(function () {
                    $old = $this->record->toArray();
                    $this->record->update(['status' => \App\Enums\BatchStatus::REVISED]);
                    AuditLogService::logUpdate($this->record, $old);
                    Notification::make()
                        ->title('Batch siap diperbaiki')
                        ->body('Silakan edit data PMKS/PSKS dan ajukan ulang setelah selesai.')
                        ->success()
                        ->send();
                    $this->refreshFormData(['status']);
                }),
        ];
    }
}
