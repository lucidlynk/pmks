<?php

namespace App\Filament\Widgets;

use App\Enums\BatchStatus;
use App\Models\PmksSubmission;
use App\Models\PsksSubmission;
use App\Models\SubmissionBatch;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class PmksPsksStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user         = auth()->user();
        $year         = now()->year;
        $villageScope = ($user?->isOperatorDesa() && $user->village_id) ? $user->village_id : 'all';
        $cacheKey     = "widget_pmks_stats_{$villageScope}_{$year}";

        $data = Cache::remember($cacheKey, 300, function () use ($user, $year) {
            $pmksQuery  = PmksSubmission::query();
            $psksQuery  = PsksSubmission::query();
            $batchQuery = SubmissionBatch::query();

            if ($user?->isOperatorDesa() && $user->village_id) {
                $pmksQuery->where('village_id', $user->village_id);
                $psksQuery->where('village_id', $user->village_id);
                $batchQuery->where('village_id', $user->village_id);
            }

            return [
                'totalPmks'     => (clone $pmksQuery)->whereHas('batch', fn ($q) => $q->where('period_year', $year))->count(),
                'totalPsks'     => (clone $psksQuery)->whereHas('batch', fn ($q) => $q->where('period_year', $year))->count(),
                'totalApproved' => (clone $batchQuery)->where('status', BatchStatus::APPROVED->value)->where('period_year', $year)->count(),
                'totalPending'  => (clone $batchQuery)->whereIn('status', [BatchStatus::SUBMITTED->value, BatchStatus::VERIFIED->value])->where('period_year', $year)->count(),
                'totalDraft'    => (clone $batchQuery)->where('status', BatchStatus::DRAFT->value)->where('period_year', $year)->count(),
            ];
        });

        return [
            Stat::make("Total PMKS {$year}", number_format($data['totalPmks']))
                ->description('Data PMKS tahun ini')
                ->color('danger')
                ->icon('heroicon-o-user-group'),

            Stat::make("Total PSKS {$year}", number_format($data['totalPsks']))
                ->description('Data PSKS tahun ini')
                ->color('success')
                ->icon('heroicon-o-building-library'),

            Stat::make('Batch Disetujui', number_format($data['totalApproved']))
                ->description("Pengajuan approved {$year}")
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Batch Menunggu', number_format($data['totalPending']))
                ->description('Sedang diproses')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            Stat::make('Batch Draft', number_format($data['totalDraft']))
                ->description('Belum diajukan')
                ->color('gray')
                ->icon('heroicon-o-document'),
        ];
    }
}
