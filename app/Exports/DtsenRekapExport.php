<?php

namespace App\Exports;

use App\Models\DtsenRekap;
use App\Models\DtsenRekapDetail;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DtsenRekapExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    private int $no = 0;

    public function __construct(
        private readonly DtsenRekap $rekap,
    ) {}

    public function query()
    {
        return DtsenRekapDetail::query()
            ->where('dtsen_rekap_id', $this->rekap->id)
            ->orderBy('kecamatan')
            ->orderBy('kelurahan');
    }

    public function title(): string
    {
        return "DTSEN {$this->rekap->periode}";
    }

    public function headings(): array
    {
        return [
            'No',
            'Kecamatan',
            'Kelurahan/Desa',
            'Jml KK',
            'Jml Jiwa',
            'D1 KK',
            'D1 Jiwa',
            'D2 KK',
            'D2 Jiwa',
            'D3 KK',
            'D3 Jiwa',
            'D4 KK',
            'D4 Jiwa',
            'D5 KK',
            'D5 Jiwa',
            'D6-10 KK',
            'D6-10 Jiwa',
            'Blm Peringkat KK',
            'Blm Peringkat Jiwa',
            'Nonaktif KK',
            'Nonaktif Jiwa',
        ];
    }

    public function map($row): array
    {
        $this->no++;

        return [
            $this->no,
            $row->kecamatan,
            $row->kelurahan,
            $row->jumlah_keluarga,
            $row->jumlah_individu,
            $row->desil1_keluarga,
            $row->desil1_individu,
            $row->desil2_keluarga,
            $row->desil2_individu,
            $row->desil3_keluarga,
            $row->desil3_individu,
            $row->desil4_keluarga,
            $row->desil4_individu,
            $row->desil5_keluarga,
            $row->desil5_individu,
            $row->desil6_10_keluarga,
            $row->desil6_10_individu,
            $row->belum_peringkat_keluarga,
            $row->belum_peringkat_individu,
            $row->nonaktif_keluarga,
            $row->nonaktif_individu,
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
            'B' => 18,
            'C' => 22,
            'D' => 10,
            'E' => 10,
            'F' => 9,
            'G' => 9,
            'H' => 9,
            'I' => 9,
            'J' => 9,
            'K' => 9,
            'L' => 9,
            'M' => 9,
            'N' => 9,
            'O' => 9,
            'P' => 11,
            'Q' => 11,
            'R' => 18,
            'S' => 18,
            'T' => 13,
            'U' => 13,
        ];
    }
}
