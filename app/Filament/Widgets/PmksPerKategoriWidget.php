<?php

namespace App\Filament\Widgets;

use App\Models\PmksCategory;
use App\Models\PmksSubmission;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PmksPerKategoriWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return 'Rekap PMKS Per Kategori — ' . now()->year;
    }

    public function table(Table $table): Table
    {
        $year = now()->year;
        $user = auth()->user();

        return $table
            ->query(function () use ($year, $user): Builder {
                $query = PmksCategory::query()
                    ->withCount([
                        'pmksSubmissions as total' => function ($q) use ($year, $user) {
                            $q->whereHas('batch', fn ($b) =>
                                $b->where('period_year', $year)
                            );
                            if ($user?->isOperatorDesa() && $user->village_id) {
                                $q->where('village_id', $user->village_id);
                            }
                        },
                        'pmksSubmissions as total_approved' => function ($q) use ($year, $user) {
                            $q->whereHas('batch', fn ($b) =>
                                $b->where('period_year', $year)
                                  ->where('status', 'approved')
                            );
                            if ($user?->isOperatorDesa() && $user->village_id) {
                                $q->where('village_id', $user->village_id);
                            }
                        },
                    ])
                    ->where('is_active', true)
                    ->orderBy('code');

                return $query;
            })
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Kategori PMKS')
                    ->searchable(),

                TextColumn::make('total')
                    ->label('Total Data')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'info' : 'gray'),

                TextColumn::make('total_approved')
                    ->label('Sudah Disetujui')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
            ])
            ->paginated(false);
    }
}
