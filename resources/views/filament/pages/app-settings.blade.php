<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->settingsForm }}
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit" color="primary">
                Simpan Pengaturan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
