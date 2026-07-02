<?php

namespace App\Filament\Widgets;

use App\Models\KisRekap;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KisStatsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        // Ambil data bulan terakhir yang tersedia
        $latest = KisRekap::query()
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->first();

        if (!$latest) {
            return [
                Stat::make('Data KIS', 'Belum ada data')
                    ->description('Silakan input rekap KIS terlebih dahulu')
                    ->color('gray')
                    ->icon('heroicon-o-heart'),
            ];
        }

        $bulanLabel = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $periode = ($bulanLabel[$latest->periode_bulan] ?? $latest->periode_bulan)
                 . ' ' . $latest->periode_tahun;

        // Tren 6 bulan terakhir untuk chart
        $tren = KisRekap::query()
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->limit(6)
            ->get()
            ->reverse()
            ->values();

        $trenTotal   = $tren->pluck('total')->toArray();
        $trenPbiApbd = $tren->pluck('pbi_apbd')->toArray();
        $trenPbiApbn = $tren->pluck('pbi_apbn')->toArray();

        return [
            Stat::make('Total Peserta KIS', number_format($latest->total))
                ->description("Periode {$periode}")
                ->color('primary')
                ->icon('heroicon-o-heart')
                ->chart($trenTotal),

            Stat::make('PBI APBD', number_format($latest->pbi_apbd))
                ->description("Periode {$periode}")
                ->color('warning')
                ->icon('heroicon-o-banknotes')
                ->chart($trenPbiApbd),

            Stat::make('PBI APBN', number_format($latest->pbi_apbn))
                ->description("Periode {$periode}")
                ->color('info')
                ->icon('heroicon-o-flag')
                ->chart($trenPbiApbn),

            Stat::make('PPU', number_format($latest->ppu))
                ->description("Pekerja Penerima Upah — {$periode}")
                ->color('success')
                ->icon('heroicon-o-briefcase'),

            Stat::make('PBPU', number_format($latest->pbpu))
                ->description("Pekerja Bukan Penerima Upah — {$periode}")
                ->color('success')
                ->icon('heroicon-o-user'),

            Stat::make('BP', number_format($latest->bp))
                ->description("Bukan Pekerja — {$periode}")
                ->color('gray')
                ->icon('heroicon-o-user-circle'),
        ];
    }
}
