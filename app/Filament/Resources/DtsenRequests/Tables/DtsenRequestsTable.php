<?php
namespace App\Filament\Resources\DtsenRequests\Tables;
use App\Enums\DtsenStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
class DtsenRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('No. Referensi')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('village.name')
                    ->label('Desa')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('residents_summary')
                    ->label('Warga')
                    ->state(function ($record): string {
                        $residents = $record->residents;
                        if ($residents->isEmpty()) {
                            return '-';
                        }
                        $first = $residents->first();
                        $summary = $first->name . ' (' . $first->nik . ')';
                        $remaining = $residents->count() - 1;
                        if ($remaining > 0) {
                            $summary .= ' +' . $remaining . ' lainnya';
                        }
                        return $summary;
                    })
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('residents', function ($q) use ($search): void {
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('nik', 'like', "%{$search}%");
                        });
                    })
                    ->tooltip(function ($record): string {
                        return $record->residents
                            ->map(fn ($r) => $r->name . ' — ' . $r->nik)
                            ->join("\n");
                    }),
                TextColumn::make('residents_count')
                    ->label('Jml')
                    ->counts('residents')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Diajukan Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->purpose),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (DtsenStatus $state) => $state->label())
                    ->color(fn (DtsenStatus $state) => $state->color()),
                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(DtsenStatus::options()),
                Filter::make('tanggal_pengajuan')
                    ->label('Tanggal Pengajuan')
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'],
                                fn ($q) => $q->whereDate('created_at', '>=', $data['dari_tanggal'])
                            )
                            ->when($data['sampai_tanggal'],
                                fn ($q) => $q->whereDate('created_at', '<=', $data['sampai_tanggal'])
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari_tanggal']) {
                            $indicators[] = 'Dari: ' . $data['dari_tanggal'];
                        }
                        if ($data['sampai_tanggal']) {
                            $indicators[] = 'Sampai: ' . $data['sampai_tanggal'];
                        }
                        return $indicators;
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
