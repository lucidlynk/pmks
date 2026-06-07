<?php

namespace App\Exports;

use App\Models\Institution;
use App\Models\PsksSubmission;
use App\Models\Resident;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PsksSubmissionExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    private int $no = 0;

    public function __construct(
        private readonly ?int $villageId = null,
        private readonly ?int $periodYear = null,
        private readonly ?string $subjectType = null,
    ) {}

    public function query()
    {
        $query = PsksSubmission::query()
            ->with(['village.kecamatan', 'category', 'batch', 'inputBy'])
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

        if ($this->subjectType) {
            $query->where('subject_type', $this->subjectType);
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
            'Jenis Subjek',
            'Nama Subjek',
            'Identitas (NIK/No. Reg)',
            'Desa / Kelurahan',
            'Kecamatan',
            'Kategori PSKS',
            'Tahun Periode',
            'Status',
            'Catatan',
            'Diinput Oleh',
            'Tanggal Input',
        ];
    }

    public function map($row): array
    {
        $this->no++;

        // Ambil data subjek
        $subjectName     = '-';
        $subjectIdentity = '-';

        if ($row->subject_type === 'person') {
            $subject         = Resident::find($row->subject_id);
            $subjectName     = $subject?->name ?? '-';
            $subjectIdentity = $subject?->nik ?? '-';
        } else {
            $subject         = Institution::find($row->subject_id);
            $subjectName     = $subject?->name ?? '-';
            $subjectIdentity = $subject?->registration_number ?? '-';
        }

        return [
            $this->no,
            $row->subject_type === 'person' ? 'Individu' : 'Lembaga',
            $subjectName,
            $subjectIdentity,
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
                    'startColor' => ['rgb' => '065F46'],
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
            'B' => 12,
            'C' => 30,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 35,
            'H' => 10,
            'I' => 12,
            'J' => 25,
            'K' => 20,
            'L' => 18,
        ];
    }

    public function title(): string
    {
        return 'Data PSKS';
    }
}
