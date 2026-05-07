<?php

use App\Enums\BatchStatus;
use App\Models\Kecamatan;
use App\Models\PmksCategory;
use App\Models\PmksSubmission;
use App\Models\PsksCategory;
use App\Models\PsksSubmission;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Models\Village;

function createApiSetup(): array
{
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa']);
    $user      = User::factory()->adminDinsos()->create();
    $batch     = SubmissionBatch::create(['village_id' => $village->id, 'submitted_by' => $user->id, 'period_year' => now()->year, 'status' => BatchStatus::APPROVED]);
    $resident  = Resident::create(['village_id' => $village->id, 'nik' => '5108011234567890', 'name' => 'Test', 'birth_place' => 'X', 'birth_date' => '1990-01-01', 'gender' => 'L']);
    $pmksCat   = PmksCategory::firstOrCreate(['code' => 'PMKS-24'], ['name' => 'Keluarga Bermasalah']);
    $psksCat   = PsksCategory::firstOrCreate(['code' => 'PSKS-J-01'], ['name' => 'PSM', 'subject_type' => 'person']);

    PmksSubmission::create(['batch_id' => $batch->id, 'village_id' => $village->id, 'resident_id' => $resident->id, 'category_id' => $pmksCat->id, 'input_by' => $user->id, 'status' => 'approved']);
    PsksSubmission::create(['batch_id' => $batch->id, 'village_id' => $village->id, 'category_id' => $psksCat->id, 'subject_type' => 'person', 'subject_id' => $resident->id, 'input_by' => $user->id, 'status' => 'approved']);

    return [$kecamatan, $village, $user, $batch, $resident];
}

it('api ringkasan mengembalikan data yang benar', function () {
    createApiSetup();

    $this->getJson('/api/v1/statistik/ringkasan?tahun=' . now()->year)
        ->assertOk()
        ->assertJsonStructure([
            'status', 'tahun', 'wilayah',
            'data' => ['total_pmks', 'total_psks', 'total_kecamatan', 'total_desa'],
            'generated_at',
        ])
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.total_pmks', 1)
        ->assertJsonPath('data.total_psks', 1);
});

it('api pmks per kecamatan mengembalikan struktur yang benar', function () {
    createApiSetup();

    $this->getJson('/api/v1/statistik/pmks?tahun=' . now()->year)
        ->assertOk()
        ->assertJsonStructure([
            'status', 'tahun', 'total_pmks',
            'data' => ['*' => ['id', 'nama_kecamatan', 'total_pmks', 'total_desa', 'desa']],
        ])
        ->assertJsonPath('total_pmks', 1);
});

it('api psks per kecamatan mengembalikan struktur yang benar', function () {
    createApiSetup();

    $this->getJson('/api/v1/statistik/psks?tahun=' . now()->year)
        ->assertOk()
        ->assertJsonStructure([
            'status', 'tahun', 'total_psks',
            'data' => ['*' => ['id', 'nama_kecamatan', 'total_psks', 'total_desa', 'desa']],
        ])
        ->assertJsonPath('total_psks', 1);
});

it('api per kecamatan mengembalikan ringkasan pmks dan psks', function () {
    createApiSetup();

    $this->getJson('/api/v1/statistik/kecamatan?tahun=' . now()->year)
        ->assertOk()
        ->assertJsonStructure([
            'status', 'tahun', 'total_pmks', 'total_psks',
            'data' => ['*' => ['id', 'nama_kecamatan', 'kode', 'total_desa', 'total_pmks', 'total_psks', 'total']],
        ]);
});

it('api per desa mengembalikan data desa', function () {
    [$kecamatan] = createApiSetup();

    $this->getJson('/api/v1/statistik/desa/' . $kecamatan->id . '?tahun=' . now()->year)
        ->assertOk()
        ->assertJsonStructure([
            'status', 'tahun', 'total_pmks', 'total_psks',
            'data' => ['*' => ['id', 'nama_desa', 'tipe', 'nama_kecamatan', 'total_pmks', 'total_psks', 'total']],
        ])
        ->assertJsonPath('total_pmks', 1)
        ->assertJsonPath('total_psks', 1);
});

it('api mendukung filter tahun', function () {
    createApiSetup();

    $this->getJson('/api/v1/statistik/ringkasan?tahun=2020')
        ->assertOk()
        ->assertJsonPath('tahun', 2020)
        ->assertJsonPath('data.total_pmks', 0);
});
