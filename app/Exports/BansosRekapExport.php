<?php

namespace App\Exports;

use App\Models\BansosMember;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BansosRekapExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    private int $no = 0;

    public function __construct(
        private readonly int $triwulan,
        private readonly int $tahun,
    ) {}

    public function collection()
    {
        $pkh = BansosMember::where('jenis_bansos', 'pkh')
            ->where('triwulan', $this->triwulan)
            ->where('tahun', $this->tahun)
            ->selectRaw('kec_name, kel_name, COUNT(*) as total')
            ->groupBy('kec_name', 'kel_name')
            ->get()
            ->keyBy(fn ($r) => $r->kec_name . '||' . $r->kel_name);

        $sembako = BansosMember::where('jenis_bansos', 'sembako')
            ->where('triwulan', $this->triwulan)
            ->where('tahun', $this->tahun)
            ->selectRaw('kec_name, kel_name, COUNT(*) as total')
            ->groupBy('kec_name', 'kel_name')
            ->get()
            ->keyBy(fn ($r) => $r->kec_name . '||' . $r->kel_name);

        // Gabung semua kunci unik kecamatan+desa
        $keys = $pkh->keys()->merge($sembako->keys())->unique()->sort();

        return $keys->map(function ($key) use ($pkh, $sembako) {
            [$kec, $kel] = explode('||', $key);
            return (object) [
                'kec_name' => $kec,
                'kel_name' => $kel,
                'pkh'      => $pkh->get($key)?->total ?? 0,
                'sembako'  => $sembako->get($key)?->total ?? 0,
            ];
        })->sortBy(['kec_name', 'kel_name'])->values();
    }

    public function title(): string
    {
        return "Rekap TW{$this->triwulan} {$this->tahun}";
    }

    public function headings(): array
    {
        return [
            'No',
            'Kecamatan',
            'Kelurahan / Desa',
            'PKH',
            'Sembako',
            'Total',
        ];
    }

    public function map($row): array
    {
        $this->no++;
        return [
            $this->no,
            $row->kec_name,
            $row->kel_name,
            $row->pkh,
            $row->sembako,
            $row->pkh + $row->sembako,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 22, 'C' => 28, 'D' => 12, 'E' => 12, 'F' => 12];
    }
}
