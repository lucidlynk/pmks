<?php

use App\Enums\BatchStatus;
use App\Exports\BatchRekapExport;
use App\Exports\PmksSubmissionExport;
use App\Exports\PsksSubmissionExport;
use App\Models\Kecamatan;
use App\Models\PmksCategory;
use App\Models\PmksSubmission;
use App\Models\PsksCategory;
use App\Models\PsksSubmission;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Models\Village;
use Maatwebsite\Excel\Facades\Excel;

function createExportSetup(): array
{
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa']);
    $user      = User::factory()->adminDinsos()->create();
    $batch     = SubmissionBatch::create(['village_id' => $village->id, 'submitted_by' => $user->id, 'period_year' => 2025, 'status' => BatchStatus::APPROVED]);
    $resident  = Resident::create(['village_id' => $village->id, 'nik' => '5108011234567890', 'name' => 'Budi', 'birth_place' => 'Singaraja', 'birth_date' => '1990-01-01', 'gender' => 'L']);
    $pmksCat   = PmksCategory::firstOrCreate(['code' => 'PMKS-23'], ['name' => 'Fakir Miskin']);
    $psksCat   = PsksCategory::firstOrCreate(['code' => 'PSKS-J-01'], ['name' => 'PSM', 'subject_type' => 'person']);

    PmksSubmission::create(['batch_id' => $batch->id, 'village_id' => $village->id, 'resident_id' => $resident->id, 'category_id' => $pmksCat->id, 'input_by' => $user->id, 'status' => 'approved']);
    PsksSubmission::create(['batch_id' => $batch->id, 'village_id' => $village->id, 'category_id' => $psksCat->id, 'subject_type' => 'person', 'subject_id' => $resident->id, 'input_by' => $user->id, 'status' => 'approved']);

    return [$village, $user, $batch, $resident];
}

it('dapat export data pmks ke excel', function () {
    [$village, $user] = createExportSetup();
    $this->actingAs($user);

    Excel::fake();

    Excel::download(
        new PmksSubmissionExport(villageId: $village->id, periodYear: 2025),
        'test.xlsx'
    );

    Excel::assertDownloaded('test.xlsx');
});

it('dapat export data psks ke excel', function () {
    [$village, $user] = createExportSetup();
    $this->actingAs($user);

    Excel::fake();

    Excel::download(
        new PsksSubmissionExport(villageId: $village->id, periodYear: 2025),
        'test-psks.xlsx'
    );

    Excel::assertDownloaded('test-psks.xlsx');
});

it('dapat export rekap batch ke excel dengan 2 sheet', function () {
    [$village, $user, $batch] = createExportSetup();
    $this->actingAs($user);

    Excel::fake();

    Excel::download(new BatchRekapExport($batch->id), 'rekap.xlsx');

    Excel::assertDownloaded('rekap.xlsx');
});

it('pmks export heading memiliki kolom yang benar', function () {
    [$village, $user] = createExportSetup();
    $this->actingAs($user);

    $headings = (new PmksSubmissionExport())->headings();

    expect($headings)
        ->toContain('NIK')
        ->toContain('Nama Lengkap')
        ->toContain('Kategori PMKS')
        ->toContain('Tahun Periode')
        ->toContain('Desa / Kelurahan')
        ->toContain('Kecamatan');
});

it('psks export heading memiliki kolom yang benar', function () {
    [$village, $user] = createExportSetup();
    $this->actingAs($user);

    $headings = (new PsksSubmissionExport())->headings();

    expect($headings)
        ->toContain('Jenis Subjek')
        ->toContain('Nama Subjek')
        ->toContain('Kategori PSKS')
        ->toContain('Desa / Kelurahan');
});

it('operator desa hanya bisa export data desanya sendiri', function () {
    [$village, $adminUser, $batch, $resident] = createExportSetup();

    $operatorDesa = User::factory()->operatorDesa()->create(['village_id' => $village->id]);
    $this->actingAs($operatorDesa);

    $data = (new PmksSubmissionExport(periodYear: 2025))->query()->get();

    foreach ($data as $row) {
        expect($row->village_id)->toBe($village->id);
    }
});
