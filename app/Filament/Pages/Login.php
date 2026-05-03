<?php

namespace App\Filament\Pages;

use App\Services\AuditLogService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    use WithRateLimiting;

    private const MAX_ATTEMPTS  = 5;
    private const DECAY_SECONDS = 900; // 15 menit

    public function authenticate(): ?LoginResponse
    {
        $email = data_get($this->form->getState(), 'email', '');

        try {
            $this->rateLimit(self::MAX_ATTEMPTS, self::DECAY_SECONDS);
        } catch (TooManyRequestsException $exception) {
            $minutes = ceil($exception->secondsUntilAvailable / 60);

            AuditLogService::logLogin(false, $email);

            Notification::make()
                ->title('Terlalu banyak percobaan login')
                ->body("Akun dikunci sementara. Coba lagi dalam {$minutes} menit.")
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'data.email' => "Terlalu banyak percobaan. Coba lagi dalam {$minutes} menit.",
            ]);
        }

        $key      = 'login_attempts_' . request()->ip();
        $attempts = Cache::get($key, 0);

        try {
            $response = parent::authenticate();

            Cache::forget($key);
            AuditLogService::logLogin(true, $email);

            return $response;

        } catch (ValidationException $e) {
            Cache::put($key, $attempts + 1, self::DECAY_SECONDS);
            AuditLogService::logLogin(false, $email);

            $remaining = self::MAX_ATTEMPTS - ($attempts + 1);

            if ($remaining > 0 && $remaining <= 3) {
                Notification::make()
                    ->title('Login gagal')
                    ->body("Sisa percobaan: {$remaining} kali sebelum akun dikunci.")
                    ->warning()
                    ->send();
            }

            throw $e;
        }
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->password()
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }
}
