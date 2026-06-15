<?php

namespace App\Exports;

use App\Models\DtsenRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DtsenRequestExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle,
    WithEvents
{
    private int $no = 0;

    public function __construct(
        private readonly DtsenRequest $request,
    ) {}

    public function collection()
    {
        return $this->request->residents()->orderBy('name')->get();
    }

    public function title(): string
    {
        return 'Daftar Warga';
    }

    public function headings(): array
    {
        return [
            // Baris info permohonan (akan diisi via WithEvents)
            ['No. Referensi', $this->request->reference_number, '', '', '', ''],
            ['Desa', $this->request->village?->name ?? '-', '', '', '', ''],
            ['Keperluan', $this->request->purpose ?? '-', '', '', '', ''],
            ['Status', $this->request->status->label(), '', '', '', ''],
            ['', '', '', '', '', ''],
            // Header tabel warga
            ['No', 'NIK', 'Nama', 'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin'],
        ];
    }

    public function map($row): array
    {
        $this->no++;

        return [
            $this->no,
            $row->nik,
            $row->name,
            $row->birth_place,
            $row->birth_date?->format('d/m/Y') ?? '-',
            $row->gender === 'L' ? 'Laki-laki' : 'Perempuan',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header tabel warga (baris 6)
            6 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1D4ED8'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Label info permohonan (kolom A baris 1-4)
            'A1:A4' => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 20,
            'C' => 35,
            'D' => 20,
            'E' => 15,
            'F' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                // Merge sel info permohonan B1:F4
                foreach (['B1:F1', 'B2:F2', 'B3:F3', 'B4:F4'] as $range) {
                    $sheet->mergeCells($range);
                }
            },
        ];
    }
}
