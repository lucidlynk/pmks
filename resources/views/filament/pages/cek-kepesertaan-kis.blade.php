<x-filament-panels::page>

    <x-filament::section>
        <x-slot name="heading">Cari NIK</x-slot>
        <x-slot name="description">Masukkan NIK 16 digit untuk melihat riwayat kepesertaan KIS.</x-slot>

        <form wire:submit="cari">
            {{ $this->cariForm }}
            <div class="mt-4">
                <x-filament::button type="submit" icon="heroicon-o-magnifying-glass">
                    Cari
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    @if ($hasSearched)
        <x-filament::section class="mt-4">
            <x-slot name="heading">Hasil Pencarian</x-slot>

            @if ($riwayat->isEmpty())
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <p class="text-gray-500 dark:text-gray-400 font-medium">
                        NIK <span class="font-bold text-gray-700 dark:text-gray-200">{{ $searchedNik }}</span> tidak ditemukan dalam data PBI APBD.
                    </p>
                </div>
            @else
                <div class="mb-4 p-4 rounded-lg bg-primary-50 dark:bg-primary-950 border border-primary-200 dark:border-primary-800">
                    <p class="text-sm text-primary-600 dark:text-primary-400">NIK ditemukan</p>
                    <p class="text-lg font-bold text-primary-800 dark:text-primary-200">{{ $namaFound }}</p>
                    <p class="text-sm text-primary-600 dark:text-primary-400">NIK: {{ $searchedNik }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Periode</th>
                                <th class="px-4 py-3">PSNOKA</th>
                                <th class="px-4 py-3">Segmen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @php
                                $bulanLabel = [
                                    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
                                    5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
                                    9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
                                ];
                            @endphp
                            @foreach ($riwayat as $index => $item)
                                <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                        {{ $bulanLabel[$item->periode_bulan] ?? $item->periode_bulan }} {{ $item->periode_tahun }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $item->psnoka }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300">
                                            {{ $item->segmen }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-sm text-gray-500">Total: <span class="font-semibold">{{ $riwayat->count() }}</span> data ditemukan.</p>
            @endif
        </x-filament::section>
    @endif

</x-filament-panels::page>
