<?php

namespace App\Filament\Resources\ApiClients\Pages;

use App\Filament\Resources\ApiClients\ApiClientResource;
use App\Models\ApiClient;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class CreateApiClient extends CreateRecord
{
    protected static string $resource = ApiClientResource::class;

    protected static ?string $title = 'Buat Token API Baru';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var ApiClient $record */
        $record = $this->record;
        $user   = auth()->user();

        // Generate token Sanctum
        $tokenName  = 'api-kominfo-' . Str::slug($record->nama_instansi) . '-' . $record->id;
        $newToken   = $user->createToken($tokenName);
        $plainToken = $newToken->plainTextToken;

        // Cari id token dan buat preview
        $accessToken  = PersonalAccessToken::findToken($plainToken);
        $tokenPreview = substr($plainToken, strpos($plainToken, '|') + 1, 8);

        $record->update([
            'token_id'      => $accessToken?->id,
            'token_preview' => $tokenPreview,
        ]);

        // Flash token ke session — ditampilkan di ViewApiClient
        session()->flash('new_api_token', $plainToken);
        session()->flash('new_api_token_instansi', $record->nama_instansi);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
