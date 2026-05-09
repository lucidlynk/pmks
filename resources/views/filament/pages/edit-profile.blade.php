<x-filament-panels::page>
    <form wire:submit="saveProfile">
        {{ $this->profileForm }}
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit">
                Simpan Profil
            </x-filament::button>
        </div>
    </form>

    <form wire:submit="savePassword" class="mt-6">
        {{ $this->passwordForm }}
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit" color="warning">
                Ubah Password
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
