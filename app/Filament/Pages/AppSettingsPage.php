<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Models\AppSetting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AppSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    protected static ?int $navigationSort = 99;
    protected string $view = 'filament.pages.app-settings';

    public ?array $settingsData = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(UserRole::ADMIN_DINSOS->value) ?? false;
    }

    public static function getNavigationLabel(): string { return 'Pengaturan Aplikasi'; }
    public static function getNavigationGroup(): string { return 'Pengaturan Sistem'; }
    public function getTitle(): string { return 'Pengaturan Aplikasi'; }

    public function mount(): void
    {
        $this->settingsForm->fill([
            'app_name'        => AppSetting::get(AppSetting::APP_NAME, config('app.name')),
            'app_description' => AppSetting::get(AppSetting::APP_DESCRIPTION, ''),
            'app_logo'        => AppSetting::get(AppSetting::APP_LOGO),
            'app_favicon'     => AppSetting::get(AppSetting::APP_FAVICON),
            'pemkab_logo'     => AppSetting::get(AppSetting::PEMKAB_LOGO),
        ]);
    }

    public function settingsForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Aplikasi')
                    ->description('Nama dan deskripsi yang tampil di seluruh halaman aplikasi.')
                    ->schema([
                        TextInput::make('app_name')
                            ->label('Nama Aplikasi')
                            ->required()
                            ->maxLength(100),
                        Textarea::make('app_description')
                            ->label('Deskripsi / Tagline')
                            ->rows(2)
                            ->maxLength(255),
                    ])
                    ->columns(1),

                Section::make('Logo & Favicon')
                    ->description('Logo utama aplikasi dan favicon browser tab.')
                    ->schema([
                        FileUpload::make('app_logo')
                            ->label('Logo Aplikasi')
                            ->image()
                            ->imagePreviewHeight('80')
                            ->disk('public')
                            ->directory('settings')
                            ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/jpeg'])
                            ->maxSize(2048)
                            ->fetchFileInformation(false)
                            ->helperText('Rekomendasi: PNG/SVG transparan, max 2MB.'),

                        FileUpload::make('app_favicon')
                            ->label('Favicon')
                            ->image()
                            ->imagePreviewHeight('48')
                            ->disk('public')
                            ->directory('settings')
                            ->acceptedFileTypes(['image/png', 'image/x-icon'])
                            ->maxSize(512)
                            ->fetchFileInformation(false)
                            ->helperText('Format ICO atau PNG 32x32px.'),
                    ])
                    ->columns(2),

                Section::make('Logo Pemerintah Kabupaten')
                    ->description('Opsional — hanya tampil di halaman utama jika diisi.')
                    ->schema([
                        FileUpload::make('pemkab_logo')
                            ->label('Logo Pemkab')
                            ->image()
                            ->imagePreviewHeight('80')
                            ->disk('public')
                            ->directory('settings')
                            ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/jpeg'])
                            ->maxSize(2048)
                            ->fetchFileInformation(false)
                            ->helperText('Kosongkan jika tidak ingin menampilkan logo pemkab.'),
                    ])
                    ->columns(1),
            ])
            ->statePath('settingsData');
    }

    public function save(): void
    {
        \Log::info('AppSettings save() called');
        $data = $this->settingsForm->getState();
        \Log::info('getState done', ['keys' => array_keys($data)]);

        AppSetting::set(AppSetting::APP_NAME,        $data['app_name']);
        \Log::info('app_name saved');
        AppSetting::set(AppSetting::APP_DESCRIPTION, $data['app_description'] ?? '');
        \Log::info('app_description saved');

        if (!empty($data['app_logo'])) {
            AppSetting::set(AppSetting::APP_LOGO, $data['app_logo']);
        } else {
            AppSetting::forget(AppSetting::APP_LOGO);
        }

        if (!empty($data['app_favicon'])) {
            AppSetting::set(AppSetting::APP_FAVICON, $data['app_favicon']);
        } else {
            AppSetting::forget(AppSetting::APP_FAVICON);
        }

        if (!empty($data['pemkab_logo'])) {
            AppSetting::set(AppSetting::PEMKAB_LOGO, $data['pemkab_logo']);
        } else {
            AppSetting::forget(AppSetting::PEMKAB_LOGO);
        }

        \Log::info('sending notification');
        session()->flash('success', 'Pengaturan berhasil disimpan');
        $this->redirect(request()->header('Referer') ?? static::getUrl(), navigate: false);
    }
}
