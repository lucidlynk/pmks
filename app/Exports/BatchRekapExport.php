<?php

namespace App\Exports;

use App\Exports\PmksSubmissionExport;
use App\Exports\PsksSubmissionExport;
use App\Models\SubmissionBatch;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BatchRekapExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly int $batchId
    ) {}

    public function sheets(): array
    {
        $batch = SubmissionBatch::find($this->batchId);

        return [
            new PmksSubmissionExport(
                villageId:  $batch?->village_id,
                periodYear: $batch?->period_year,
            ),
            new PsksSubmissionExport(
                villageId:  $batch?->village_id,
                periodYear: $batch?->period_year,
            ),
        ];
    }
}
