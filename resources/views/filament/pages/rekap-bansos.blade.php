<x-filament-panels::page>

    {{-- Filter Periode --}}
    <x-filament::section>
        <div class="flex flex-wrap items-center gap-4">
            <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">Periode:</span>
            <div class="flex flex-wrap gap-2">
                @forelse ($periodeList as $p)
                    <button
                        wire:click="setPeriode({{ $p['triwulan'] }}, {{ $p['tahun'] }})"
                        @class([
                            'rounded-lg px-4 py-2 text-sm font-semibold ring-1 transition-all',
                            'bg-primary-600 text-white ring-primary-600 shadow-sm'
                                => $triwulan === $p['triwulan'] && $tahun === $p['tahun'],
                            'bg-white text-gray-600 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-700'
                                => !($triwulan === $p['triwulan'] && $tahun === $p['tahun']),
                        ])>
                        {{ $p['label'] }}
                    </button>
                @empty
                    <p class="text-sm italic text-gray-400">
                        Belum ada data — import data bansos terlebih dahulu.
                    </p>
                @endforelse
            </div>
            <div wire:loading class="flex items-center gap-1.5 text-sm text-gray-400">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Memuat…
            </div>
        </div>
    </x-filament::section>

    @if ($hasData)
        {{-- Kartu ringkasan --}}
        <x-filament::section>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-primary-600 dark:text-primary-400">Total PKH</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-gray-100">
                        {{ number_format($totalKab['pkh'], 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-success-600 dark:text-success-400">Total Sembako</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-gray-100">
                        {{ number_format($totalKab['sembako'], 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-warning-600 dark:text-warning-400">Total Penerima</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-gray-100">
                        {{ number_format($totalKab['total'], 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Jumlah Desa</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-gray-100">
                        {{ number_format($totalKab['desa'], 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </x-filament::section>
    @endif

    {{-- Tabel Filament --}}
    {{ $this->table }}

</x-filament-panels::page>
