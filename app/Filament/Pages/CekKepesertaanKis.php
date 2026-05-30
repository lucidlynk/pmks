<?php

namespace App\Filament\Pages;

use App\Models\KisPbiApbdMember;
use BackedEnum;
use Filament\Actions\Action as HeaderAction;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class CekKepesertaanKis extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.cek-kepesertaan-kis';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'cek-kepesertaan-kis';
    protected static ?string $title = 'Cek Kepesertaan KIS';

    public ?array $data = [];
    public ?string $searchedNik = null;
    public ?string $namaFound   = null;
    public Collection $riwayat;
    public bool $hasSearched = false;

    public static function getNavigationLabel(): string
    {
        return 'Cek Kepesertaan KIS';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Data KIS / JKN';
    }

    public function mount(): void
    {
        $this->riwayat = collect();
        $this->cariForm->fill();
    }

    public function cariForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                TextInput::make('nik')
                    ->label('NIK')
                    ->placeholder('Masukkan 16 digit NIK')
                    ->numeric()
                    ->minLength(16)
                    ->maxLength(16)
                    ->required(),
            ]);
    }

    public function getHeaderActions(): array
    {
        return [];
    }

    public function cari(): void
    {
        $this->cariForm->validate();

        $nik               = $this->data['nik'];
        $this->searchedNik = $nik;
        $this->hasSearched = true;

        $this->riwayat = KisPbiApbdMember::where('nik', $nik)
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->get();

        $this->namaFound = $this->riwayat->first()?->nama;
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
