<?php

namespace App\Filament\Widgets;

use App\Models\PsksCategory;
use App\Models\PsksSubmission;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PsksPerKategoriWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return 'Rekap PSKS Per Kategori — ' . now()->year;
    }

    public function table(Table $table): Table
    {
        $year = now()->year;
        $user = auth()->user();

        return $table
            ->query(function () use ($year, $user): Builder {
                return PsksCategory::query()
                    ->withCount([
                        'psksSubmissions as total' => function ($q) use ($year, $user) {
                            $q->whereHas('batch', fn ($b) =>
                                $b->where('period_year', $year)
                            );
                            if ($user?->isOperatorDesa() && $user->village_id) {
                                $q->where('village_id', $user->village_id);
                            }
                        },
                        'psksSubmissions as total_approved' => function ($q) use ($year, $user) {
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
            })
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Kategori PSKS')
                    ->searchable(),

                TextColumn::make('subject_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'person'      => 'info',
                        'institution' => 'warning',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'person'      => 'Individu',
                        'institution' => 'Lembaga',
                        default       => $state,
                    }),

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
