<?php

namespace App\Exports;

use App\Models\PmksSubmission;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PmksSubmissionExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    public function __construct(
        private readonly ?int $villageId = null,
        private readonly ?int $periodYear = null,
        private readonly ?int $categoryId = null,
    ) {}

    public function query()
    {
        $query = PmksSubmission::query()
            ->with(['resident', 'village.kecamatan', 'category', 'batch', 'inputBy'])
            ->orderBy('village_id')
            ->orderBy('created_at');

        if ($this->villageId) {
            $query->where('village_id', $this->villageId);
        }

        if ($this->periodYear) {
            $query->whereHas('batch', fn ($q) =>
                $q->where('period_year', $this->periodYear)
            );
        }

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        // Scope wilayah untuk Operator Desa
        $user = auth()->user();
        if ($user?->isOperatorDesa() && $user->village_id) {
            $query->where('village_id', $user->village_id);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'NIK',
            'Nama Lengkap',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Desa / Kelurahan',
            'Kecamatan',
            'Kategori PMKS',
            'Tahun Periode',
            'Status',
            'Catatan',
            'Diinput Oleh',
            'Tanggal Input',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->resident?->nik ?? '-',
            $row->resident?->name ?? '-',
            $row->resident?->birth_place ?? '-',
            $row->resident?->birth_date?->format('d/m/Y') ?? '-',
            $row->resident?->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            $row->village?->name ?? '-',
            $row->village?->kecamatan?->name ?? '-',
            $row->category?->name ?? '-',
            $row->batch?->period_year ?? '-',
            match($row->status) {
                'draft'     => 'Draft',
                'submitted' => 'Diajukan',
                'approved'  => 'Disetujui',
                'rejected'  => 'Ditolak',
                default     => $row->status,
            },
            $row->notes ?? '-',
            $row->inputBy?->name ?? '-',
            $row->created_at?->format('d/m/Y H:i') ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 20,
            'C' => 25,
            'D' => 15,
            'E' => 15,
            'F' => 12,
            'G' => 20,
            'H' => 20,
            'I' => 35,
            'J' => 10,
            'K' => 12,
            'L' => 25,
            'M' => 20,
            'N' => 18,
        ];
    }

    public function title(): string
    {
        return 'Data PMKS';
    }
}
