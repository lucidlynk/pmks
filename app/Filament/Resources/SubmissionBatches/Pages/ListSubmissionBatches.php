<?php

namespace App\Filament\Resources\SubmissionBatches\Pages;

use App\Enums\BatchStatus;
use App\Enums\UserRole;
use App\Filament\Resources\SubmissionBatches\SubmissionBatchResource;
use App\Jobs\BulkApproveBatchJob;
use App\Jobs\BulkCreateBatchJob;
use App\Jobs\BulkSubmitBatchJob;
use App\Models\Kecamatan;
use App\Models\Village;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSubmissionBatches extends ListRecords
{
    protected static string $resource = SubmissionBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('bulkCreateBatch')
                ->label('Buat Batch Massal')
                ->icon('heroicon-o-squares-plus')
                ->color('warning')
                ->visible(fn () => auth()->user()?->hasRole(UserRole::ADMIN_DINSOS->value))
                ->form([
                    Select::make('scope')
                        ->label('Cakupan')
                        ->options([
                            'all'       => 'Seluruh Desa & Kelurahan',
                            'kecamatan' => 'Per Kecamatan',
                        ])
                        ->required()
                        ->default('all')
                        ->live(),

                    Select::make('kecamatan_ids')
                        ->label('Pilih Kecamatan')
                        ->multiple()
                        ->options(Kecamatan::where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->visible(fn (callable $get) => $get('scope') === 'kecamatan'),

                    TextInput::make('period_year')
                        ->label('Tahun Periode')
                        ->required()
                        ->numeric()
                        ->default(now()->year)
                        ->minValue(2020)
                        ->maxValue(2099),
                ])
                ->requiresConfirmation()
                ->modalHeading('Buat Batch Massal')
                ->modalDescription('Batch DRAFT akan dibuat untuk setiap desa/kelurahan yang belum memiliki batch di tahun tersebut.')
                ->modalSubmitActionLabel('Buat Batch')
                ->action(function (array $data) {
                    $villageQuery = Village::where('is_active', true);

                    if ($data['scope'] === 'kecamatan' && !empty($data['kecamatan_ids'])) {
                        $villageQuery->whereIn('kecamatan_id', $data['kecamatan_ids']);
                    }

                    $villageIds = $villageQuery->pluck('id')->toArray();

                    if (empty($villageIds)) {
                        Notification::make()
                            ->title('Tidak ada desa ditemukan')
                            ->danger()
                            ->send();
                        return;
                    }

                    BulkCreateBatchJob::dispatch(
                        $villageIds,
                        (int) $data['period_year'],
                        auth()->id(),
                    );

                    Notification::make()
                        ->title('Proses Bulk Create Dimulai')
                        ->body('Membuat batch untuk ' . count($villageIds) . ' desa/kelurahan.')
                        ->success()
                        ->send();
                }),

            Action::make('bulkSubmitBatch')
                ->label('Ajukan Semua Draft')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->visible(fn () => auth()->user()?->hasRole(UserRole::ADMIN_DINSOS->value))
                ->form([
                    TextInput::make('period_year')
                        ->label('Tahun Periode')
                        ->required()
                        ->numeric()
                        ->default(now()->year),

                    Select::make('scope')
                        ->label('Cakupan')
                        ->options([
                            'all'       => 'Seluruh Desa & Kelurahan',
                            'kecamatan' => 'Per Kecamatan',
                        ])
                        ->required()
                        ->default('all')
                        ->live(),

                    Select::make('kecamatan_ids')
                        ->label('Pilih Kecamatan')
                        ->multiple()
                        ->options(Kecamatan::where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->visible(fn (callable $get) => $get('scope') === 'kecamatan'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Ajukan Batch Serentak')
                ->modalDescription('Semua batch DRAFT/REVISED pada tahun dan cakupan yang dipilih akan diajukan.')
                ->modalSubmitActionLabel('Ajukan Serentak')
                ->action(function (array $data) {
                    $query = \App\Models\SubmissionBatch::where('period_year', $data['period_year'])
                        ->whereIn('status', [BatchStatus::DRAFT->value, BatchStatus::REVISED->value]);

                    if ($data['scope'] === 'kecamatan' && !empty($data['kecamatan_ids'])) {
                        $villageIds = Village::where('is_active', true)
                            ->whereIn('kecamatan_id', $data['kecamatan_ids'])
                            ->pluck('id');
                        $query->whereIn('village_id', $villageIds);
                    }

                    $batchIds = $query->pluck('id')->toArray();

                    if (empty($batchIds)) {
                        Notification::make()
                            ->title('Tidak ada batch yang bisa diajukan')
                            ->warning()
                            ->send();
                        return;
                    }

                    BulkSubmitBatchJob::dispatch($batchIds, auth()->id());

                    Notification::make()
                        ->title('Proses Bulk Submit Dimulai')
                        ->body(count($batchIds) . ' batch sedang diproses.')
                        ->success()
                        ->send();
                }),

            Action::make('bulkApproveBatch')
                ->label('Setujui Semua Terverifikasi')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => auth()->user()?->hasRole(UserRole::ADMIN_DINSOS->value))
                ->form([
                    TextInput::make('period_year')
                        ->label('Tahun Periode')
                        ->required()
                        ->numeric()
                        ->default(now()->year),

                    Select::make('scope')
                        ->label('Cakupan')
                        ->options([
                            'all'       => 'Seluruh Desa & Kelurahan',
                            'kecamatan' => 'Per Kecamatan',
                        ])
                        ->required()
                        ->default('all')
                        ->live(),

                    Select::make('kecamatan_ids')
                        ->label('Pilih Kecamatan')
                        ->multiple()
                        ->options(Kecamatan::where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->visible(fn (callable $get) => $get('scope') === 'kecamatan'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Setujui Batch Serentak')
                ->modalDescription('Semua batch VERIFIED pada tahun dan cakupan yang dipilih akan disetujui.')
                ->modalSubmitActionLabel('Setujui Serentak')
                ->action(function (array $data) {
                    $query = \App\Models\SubmissionBatch::where('period_year', $data['period_year'])
                        ->where('status', BatchStatus::VERIFIED->value);

                    if ($data['scope'] === 'kecamatan' && !empty($data['kecamatan_ids'])) {
                        $villageIds = Village::where('is_active', true)
                            ->whereIn('kecamatan_id', $data['kecamatan_ids'])
                            ->pluck('id');
                        $query->whereIn('village_id', $villageIds);
                    }

                    $batchIds = $query->pluck('id')->toArray();

                    if (empty($batchIds)) {
                        Notification::make()
                            ->title('Tidak ada batch yang bisa disetujui')
                            ->warning()
                            ->send();
                        return;
                    }

                    BulkApproveBatchJob::dispatch($batchIds, auth()->id());

                    Notification::make()
                        ->title('Proses Bulk Approve Dimulai')
                        ->body(count($batchIds) . ' batch sedang diproses.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
