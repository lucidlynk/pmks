<?php

namespace App\Filament\Pages;

use App\Services\AuditLogService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;
    protected static ?string $navigationLabel = 'Profil Saya';
    protected static ?string $title           = 'Profil Saya';
    protected static ?int $navigationSort     = 99;
    protected string $view = 'filament.pages.edit-profile';

    public ?array $profileData   = [];
    public ?array $passwordData  = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->profileForm->fill([
            'name'  => $user->name,
            'email' => $user->email,
        ]);
    }

    public function profileForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->description('Perbarui nama dan email akun Anda.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique('users', 'email', ignorable: Auth::user())
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('profileData');
    }

    public function passwordForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ganti Password')
                    ->description('Pastikan password baru minimal 8 karakter.')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('Password Saat Ini')
                            ->password()
                            ->required()
                            ->currentPassword(),

                        TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->required()
                            ->rule(Password::min(8))
                            ->different('current_password'),

                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password Baru')
                            ->password()
                            ->required()
                            ->same('password'),
                    ]),
            ])
            ->statePath('passwordData');
    }

    public function saveProfile(): void
    {
        $data = $this->profileForm->getState();
        $user = Auth::user();

        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        AuditLogService::log(
            action: 'update_profile',
            model: $user,
            newValues: ['name' => $data['name'], 'email' => $data['email']],
        );

        Notification::make()
            ->title('Profil berhasil diperbarui')
            ->success()
            ->send();
    }

    public function savePassword(): void
    {
        $data = $this->passwordForm->getState();
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        AuditLogService::logResetPassword($user);

        $this->passwordForm->fill();

        Notification::make()
            ->title('Password berhasil diubah')
            ->success()
            ->send();
    }

    protected function getForms(): array
    {
        return ['profileForm', 'passwordForm'];
    }
}
