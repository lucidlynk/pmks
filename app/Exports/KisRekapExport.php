<?php

namespace App\Exports;

use App\Models\KisRekap;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KisRekapExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    private int $no = 0;

    public function __construct(
        private readonly ?int $tahun = null,
    ) {}

    public function query()
    {
        return KisRekap::query()
            ->when($this->tahun, fn ($q) => $q->where('periode_tahun', $this->tahun))
            ->orderBy('periode_tahun')
            ->orderBy('periode_bulan');
    }

    public function title(): string
    {
        return 'Rekap KIS';
    }

    public function headings(): array
    {
        return [
            'No',
            'Periode',
            'PBI APBD',
            'PBI APBN',
            'PPU',
            'PBPU',
            'BP',
            'Total',
        ];
    }

    public function map($row): array
    {
        $this->no++;

        $bulanLabel = [
            1 => 'Januari',   2 => 'Februari',  3 => 'Maret',
            4 => 'April',     5 => 'Mei',        6 => 'Juni',
            7 => 'Juli',      8 => 'Agustus',    9 => 'September',
            10 => 'Oktober',  11 => 'November',  12 => 'Desember',
        ];

        return [
            $this->no,
            ($bulanLabel[$row->periode_bulan] ?? $row->periode_bulan) . ' ' . $row->periode_tahun,
            $row->pbi_apbd,
            $row->pbi_apbn,
            $row->ppu,
            $row->pbpu,
            $row->bp,
            $row->total,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1D4ED8'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 20,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
        ];
    }
}
