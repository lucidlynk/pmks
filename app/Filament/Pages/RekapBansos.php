<?php

namespace App\Filament\Pages;

use App\Exports\BansosRekapExport;
use App\Models\BansosMember;
use App\Models\BansosImport;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class RekapBansos extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view                               = 'filament.pages.rekap-bansos';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;
    protected static ?string $navigationLabel            = 'Rekap Bansos';
    protected static ?string $title                     = 'Rekap Bansos PKH & Sembako';
    protected static ?string $slug                      = 'rekap-bansos';
    protected static ?int    $navigationSort            = 2;

    public int $triwulan = 0;
    public int $tahun    = 0;

    public array      $totalKab   = ['pkh' => 0, 'sembako' => 0, 'total' => 0, 'desa' => 0];
    public bool       $hasData    = false;
    public Collection $periodeList;

    public static function getNavigationGroup(): string
    {
        return 'Data Bansos';
    }

    public function mount(): void
    {
        $this->periodeList = collect();
        $this->loadPeriodeList();
        if ($first = $this->periodeList->first()) {
            $this->triwulan = $first['triwulan'];
            $this->tahun    = $first['tahun'];
        }
        $this->refreshStats();
    }

    public function loadPeriodeList(): void
    {
        $this->periodeList = BansosImport::where('status', 'done')
            ->select('triwulan', 'tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->orderByDesc('triwulan')
            ->get()
            ->map(fn ($r) => [
                'label'    => "TW{$r->triwulan} {$r->tahun}",
                'triwulan' => $r->triwulan,
                'tahun'    => $r->tahun,
            ]);
    }

    public function setPeriode(int $triwulan, int $tahun): void
    {
        $this->triwulan = $triwulan;
        $this->tahun    = $tahun;
        $this->refreshStats();
        $this->resetTable();
    }

    public function refreshStats(): void
    {
        if (! $this->triwulan || ! $this->tahun) {
            $this->totalKab = ['pkh' => 0, 'sembako' => 0, 'total' => 0, 'desa' => 0];
            $this->hasData  = false;
            return;
        }

        $stats = BansosMember::where('triwulan', $this->triwulan)
            ->where('tahun', $this->tahun)
            ->selectRaw("
                SUM(CASE WHEN jenis_bansos = 'pkh'     THEN 1 ELSE 0 END) as pkh,
                SUM(CASE WHEN jenis_bansos = 'sembako' THEN 1 ELSE 0 END) as sembako,
                COUNT(*) as total,
                COUNT(DISTINCT CONCAT(kec_name,'||',kel_name)) as desa
            ")
            ->first();

        $this->hasData  = ($stats?->total ?? 0) > 0;
        $this->totalKab = [
            'pkh'     => (int) ($stats?->pkh     ?? 0),
            'sembako' => (int) ($stats?->sembako  ?? 0),
            'total'   => (int) ($stats?->total    ?? 0),
            'desa'    => (int) ($stats?->desa     ?? 0),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getRekapQuery())
            ->columns([
                TextColumn::make('kec_name')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kel_name')
                    ->label('Kelurahan / Desa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pkh')
                    ->label('PKH')
                    ->numeric(thousandsSeparator: '.')
                    ->sortable()
                    ->color('primary')
                    ->alignEnd()
                    ->summarize(Sum::make()->label('Total')->numeric(thousandsSeparator: '.')),

                TextColumn::make('sembako')
                    ->label('Sembako')
                    ->numeric(thousandsSeparator: '.')
                    ->sortable()
                    ->color('success')
                    ->alignEnd()
                    ->summarize(Sum::make()->label('Total')->numeric(thousandsSeparator: '.')),

                TextColumn::make('total')
                    ->label('Total')
                    ->numeric(thousandsSeparator: '.')
                    ->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->alignEnd()
                    ->summarize(Sum::make()->label('Total')->numeric(thousandsSeparator: '.')),
            ])
            ->filters([
                SelectFilter::make('kec_name')
                    ->label('Kecamatan')
                    ->options(fn () => $this->getKecamatanOptions())
                    ->searchable(),
            ])
            ->defaultSort('kec_name')
            ->striped()
            ->paginated([25, 50, 100, 'all'])
            ->emptyStateHeading('Belum ada data')
            ->emptyStateDescription('Pilih periode atau import data bansos terlebih dahulu.')
            ->emptyStateIcon('heroicon-o-inbox');
    }

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model|array $record): string
    {
        $kec = is_array($record) ? $record['kec_name'] : $record->kec_name;
        $kel = is_array($record) ? $record['kel_name'] : $record->kel_name;
        return $kec . '||' . $kel;
    }

    protected function getRekapQuery(): Builder
    {
        return BansosMember::where('triwulan', $this->triwulan ?: 0)
            ->where('tahun', $this->tahun ?: 0)
            ->selectRaw("
                kec_name,
                kel_name,
                SUM(CASE WHEN jenis_bansos = 'pkh'     THEN 1 ELSE 0 END) as pkh,
                SUM(CASE WHEN jenis_bansos = 'sembako' THEN 1 ELSE 0 END) as sembako,
                COUNT(*) as total
            ")
            ->groupBy('kec_name', 'kel_name');
    }

    protected function getKecamatanOptions(): array
    {
        return BansosMember::where('triwulan', $this->triwulan)
            ->where('tahun', $this->tahun)
            ->distinct()
            ->orderBy('kec_name')
            ->pluck('kec_name', 'kec_name')
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadExcel')
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => $this->hasData)
                ->action(function () {
                    $filename = "Rekap_Bansos_TW{$this->triwulan}_{$this->tahun}.xlsx";
                    return Excel::download(
                        new BansosRekapExport($this->triwulan, $this->tahun),
                        $filename
                    );
                }),
        ];
    }
}
