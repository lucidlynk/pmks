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

    // Set ke 4 agar user bisa salah 3 kali berturut-turut. 
    // Percobaan ke-4 yang akan memicu "TooManyRequestsException".
    private const MAX_ATTEMPTS  = 4; 
    private const DECAY_SECONDS = 900; // 15 menit

    public function authenticate(): ?LoginResponse
    {
        $email = data_get($this->form->getState(), 'email', '');

        try {
            // Ini akan membolehkan 3 kali eksekusi, dan throw exception pada yang ke-4
            $this->rateLimit(self::MAX_ATTEMPTS, self::DECAY_SECONDS);
        } catch (TooManyRequestsException $exception) {
            $minutes = ceil($exception->secondsUntilAvailable / 60);

            AuditLogService::logLogin(false, $email);

            Notification::make()
                ->title('Akun Terkunci')
                ->body("Anda telah salah 3 kali berturut-turut. Silakan coba lagi dalam {$minutes} menit.")
                ->danger()
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
                'data.email' => "Batas percobaan habis. Tunggu {$minutes} menit.",
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
            $newAttempts = $attempts + 1;
            Cache::put($key, $newAttempts, self::DECAY_SECONDS);
            AuditLogService::logLogin(false, $email);

            // Hitung sisa toleransi untuk user (3 - jumlah salah)
            $remainingToleransi = 3 - $newAttempts;

            if ($remainingToleransi > 0) {
                Notification::make()
                    ->title('Login Gagal')
                    ->body("Email atau password salah. Sisa kesempatan: {$remainingToleransi} kali.")
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
