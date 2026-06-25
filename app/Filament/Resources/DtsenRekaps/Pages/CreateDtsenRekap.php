<?php

namespace App\Filament\Resources\DtsenRekaps\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\DtsenRekaps\DtsenRekapResource;
use App\Models\DtsenRekap;
use App\Services\AuditLogService;
use App\Services\DtsenRekapImportService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDtsenRekap extends CreateRecord
{
    protected static string $resource = DtsenRekapResource::class;
    protected static ?string $title = 'Upload Rekap DTSEN';

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->hasRole(UserRole::ADMIN_DINSOS->value) ?? false;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by'] = Auth::id();
        return $data;
    }

    protected function beforeCreate(): void
    {
        $existing = DtsenRekap::where('bulan', $this->data['bulan'])
            ->where('tahun', $this->data['tahun'])
            ->first();

        if ($existing) {
            $existing->details()->delete();
            $existing->forceDelete();
        }
    }

    protected function afterCreate(): void
    {
        $result = app(DtsenRekapImportService::class)->import($this->record);

        if ($result['success']) {
            Notification::make()
                ->title('Data berhasil diparsing')
                ->body("Total {$result['rows_imported']} baris data berhasil diimport.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Peringatan: Parsing gagal')
                ->body($result['message'] . ' File tetap tersimpan.')
                ->warning()
                ->send();
        }

        AuditLogService::log(
            action: 'create',
            model: $this->record,
            newValues: $this->record->toArray(),
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Rekap DTSEN berhasil diupload';
    }
}
