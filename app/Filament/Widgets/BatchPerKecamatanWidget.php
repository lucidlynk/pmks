<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\Kecamatan;
use App\Models\SubmissionBatch;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class BatchPerKecamatanWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    // Hanya tampil untuk Admin & Operator Bidang
    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole([
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
        ]) ?? false;
    }

    public function getTableHeading(): string
    {
        return 'Status Pengajuan Per Kecamatan — ' . now()->year;
    }

    public function table(Table $table): Table
    {
        $year = now()->year;

        return $table
            ->query(
                Kecamatan::query()
                    ->withCount([
                        'villages as total_desa',
                        'villages as desa_sudah_submit' => function ($q) use ($year) {
                            $q->whereHas('submissionBatches', fn ($b) =>
                                $b->where('period_year', $year)
                                  ->whereNotIn('status', ['draft'])
                            );
                        },
                        'villages as desa_approved' => function ($q) use ($year) {
                            $q->whereHas('submissionBatches', fn ($b) =>
                                $b->where('period_year', $year)
                                  ->where('status', 'approved')
                            );
                        },
                    ])
                    ->where('is_active', true)
                    ->orderBy('name')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Kecamatan')
                    ->sortable(),

                TextColumn::make('total_desa')
                    ->label('Total Desa')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('desa_sudah_submit')
                    ->label('Sudah Submit')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'info' : 'gray')
                    ->alignCenter(),

                TextColumn::make('desa_approved')
                    ->label('Sudah Disetujui')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->alignCenter(),

                TextColumn::make('progress')
                    ->label('Progress')
                    ->getStateUsing(function ($record) {
                        if ($record->total_desa === 0) return '0%';
                        $pct = round(($record->desa_approved / $record->total_desa) * 100);
                        return "{$pct}%";
                    })
                    ->badge()
                    ->color(function ($record) {
                        if ($record->total_desa === 0) return 'gray';
                        $pct = ($record->desa_approved / $record->total_desa) * 100;
                        return match(true) {
                            $pct >= 80 => 'success',
                            $pct >= 50 => 'warning',
                            default    => 'danger',
                        };
                    })
                    ->alignCenter(),
            ])
            ->paginated(false);
    }
}
