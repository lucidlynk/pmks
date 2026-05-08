<?php

use App\Enums\BatchStatus;
use App\Models\Kecamatan;
use App\Models\PmksCategory;
use App\Models\PmksSubmission;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Models\Village;
use App\Rules\PmksAgeRule;

function createAgeTestSetup(): array
{
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa']);
    $user      = User::factory()->operatorDesa()->create(['village_id' => $village->id]);
    $batch     = SubmissionBatch::create(['village_id' => $village->id, 'submitted_by' => $user->id, 'period_year' => 2025, 'status' => BatchStatus::DRAFT]);
    return [$village, $user, $batch];
}

it('PMKS-01 hanya untuk usia 0-5 tahun', function () {
    $category = PmksCategory::create(['code' => 'PMKS-01', 'name' => 'Balita', 'min_age' => 0, 'max_age' => 5]);
    expect($category->min_age)->toBe(0)->and($category->max_age)->toBe(5);
});

it('PMKS-02 sampai PMKS-07 hanya untuk usia 6-18 tahun', function () {
    foreach (['PMKS-02', 'PMKS-03', 'PMKS-04', 'PMKS-05', 'PMKS-06', 'PMKS-07'] as $code) {
        $category = PmksCategory::create(['code' => $code, 'name' => "Kategori {$code}", 'min_age' => 6, 'max_age' => 18]);
        expect($category->min_age)->toBe(6)->and($category->max_age)->toBe(18);
    }
});

it('kategori di luar aturan usia tidak ada pembatasan', function () {
    $category = PmksCategory::create(['code' => 'PMKS-23', 'name' => 'Fakir Miskin', 'gender_restriction' => 'P']);
    expect($category->min_age)->toBeNull()->and($category->max_age)->toBeNull();
});

it('penduduk usia 3 tahun bisa masuk PMKS-01', function () {
    [$village, $user, $batch] = createAgeTestSetup();

    $category = PmksCategory::create(['code' => 'PMKS-01', 'name' => 'Kemiskinan Balita', 'min_age' => 0, 'max_age' => 5]);
    $resident = Resident::create([
        'village_id'  => $village->id,
        'nik'         => '1111111111111111',
        'name'        => 'Balita Test',
        'birth_place' => 'Singaraja',
        'birth_date'  => now()->subYears(3)->format('Y-m-d'),
        'gender'      => 'L',
    ]);

    $this->actingAs($user);

    \Livewire\Livewire::test(\App\Filament\Resources\PmksSubmissions\Pages\CreatePmksSubmission::class)
        ->fillForm([
            'batch_id'         => $batch->id,
            'village_id'       => $village->id,
            'resident_id'      => $resident->id,
            'category_id'      => $category->id,
            'disability_types' => null,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(PmksSubmission::where('resident_id', $resident->id)->exists())->toBeTrue();
});

it('penduduk usia 10 tahun tidak bisa masuk PMKS-01', function () {
    [$village, $user, $batch] = createAgeTestSetup();

    $category = PmksCategory::create(['code' => 'PMKS-01', 'name' => 'Kemiskinan Balita', 'min_age' => 0, 'max_age' => 5]);
    $resident = Resident::create([
        'village_id'  => $village->id,
        'nik'         => '2222222222222222',
        'name'        => 'Anak Test',
        'birth_place' => 'Singaraja',
        'birth_date'  => now()->subYears(10)->format('Y-m-d'),
        'gender'      => 'L',
    ]);

    $passed = true;
    (new PmksAgeRule($resident->id, $category->id))
        ->validate('category_id', $category->id, function () use (&$passed) {
            $passed = false;
        });

    expect($passed)->toBeFalse();
});

it('penduduk usia 15 tahun bisa masuk PMKS-02', function () {
    [$village, $user, $batch] = createAgeTestSetup();

    $category = PmksCategory::create(['code' => 'PMKS-02', 'name' => 'Keterlantaran Anak', 'min_age' => 6, 'max_age' => 18]);
    $resident = Resident::create([
        'village_id'  => $village->id,
        'nik'         => '3333333333333333',
        'name'        => 'Remaja Test',
        'birth_place' => 'Singaraja',
        'birth_date'  => now()->subYears(15)->format('Y-m-d'),
        'gender'      => 'P',
    ]);

    $passed = true;
    (new PmksAgeRule($resident->id, $category->id))
        ->validate('category_id', $category->id, function () use (&$passed) {
            $passed = false;
        });

    expect($passed)->toBeTrue();
});

it('penduduk usia 25 tahun tidak bisa masuk PMKS-02 sampai PMKS-07', function () {
    [$village, $user, $batch] = createAgeTestSetup();

    $resident = Resident::create([
        'village_id'  => $village->id,
        'nik'         => '4444444444444444',
        'name'        => 'Dewasa Test',
        'birth_place' => 'Singaraja',
        'birth_date'  => now()->subYears(25)->format('Y-m-d'),
        'gender'      => 'L',
    ]);

    foreach (['PMKS-02', 'PMKS-03', 'PMKS-04', 'PMKS-05', 'PMKS-06', 'PMKS-07'] as $code) {
        $category = PmksCategory::firstOrCreate(
            ['code' => $code],
            ['name' => "Kategori {$code}", 'min_age' => 6, 'max_age' => 18]
        );

        $passed = true;
        (new PmksAgeRule($resident->id, $category->id))
            ->validate('category_id', $category->id, function () use (&$passed) {
                $passed = false;
            });

        expect($passed)->toBeFalse("Kategori {$code} seharusnya ditolak untuk usia 25 tahun");
    }
});

it('PMKS-08 hanya untuk usia 60 tahun ke atas', function () {
    // max_age null = tidak ada batas atas
    $category = PmksCategory::create(['code' => 'PMKS-08', 'name' => 'Lansia', 'min_age' => 60, 'max_age' => null]);
    expect($category->min_age)->toBe(60)->and($category->max_age)->toBeNull();
});

it('penduduk usia 65 tahun bisa masuk PMKS-08', function () {
    [$village, $user, $batch] = createAgeTestSetup();

    $category = PmksCategory::firstOrCreate(
        ['code' => 'PMKS-08'],
        ['name' => 'Lansia', 'min_age' => 60, 'max_age' => null]
    );
    $resident = Resident::create([
        'village_id'  => $village->id,
        'nik'         => '5555555555555555',
        'name'        => 'Lansia Test',
        'birth_place' => 'Singaraja',
        'birth_date'  => now()->subYears(65)->format('Y-m-d'),
        'gender'      => 'L',
    ]);

    $passed = true;
    (new PmksAgeRule($resident->id, $category->id))
        ->validate('category_id', $category->id, function () use (&$passed) {
            $passed = false;
        });

    expect($passed)->toBeTrue();
});

it('penduduk usia 30 tahun tidak bisa masuk PMKS-08', function () {
    [$village, $user, $batch] = createAgeTestSetup();

    $category = PmksCategory::firstOrCreate(
        ['code' => 'PMKS-08'],
        ['name' => 'Lansia', 'min_age' => 60, 'max_age' => null]
    );
    $resident = Resident::create([
        'village_id'  => $village->id,
        'nik'         => '6666666666666666',
        'name'        => 'Dewasa Test',
        'birth_place' => 'Singaraja',
        'birth_date'  => now()->subYears(30)->format('Y-m-d'),
        'gender'      => 'L',
    ]);

    $passed = true;
    (new PmksAgeRule($resident->id, $category->id))
        ->validate('category_id', $category->id, function () use (&$passed) {
            $passed = false;
        });

    expect($passed)->toBeFalse();
});

it('PMKS-23 hanya untuk perempuan', function () {
    $category = PmksCategory::create(['code' => 'PMKS-23', 'name' => 'Fakir Miskin', 'gender_restriction' => 'P']);
    expect($category->gender_restriction)->toBe('P');
});

it('penduduk perempuan bisa masuk PMKS-23', function () {
    [$village, $user, $batch] = createAgeTestSetup();

    $category = PmksCategory::firstOrCreate(
        ['code' => 'PMKS-23'],
        ['name' => 'Fakir Miskin', 'gender_restriction' => 'P']
    );
    $resident = Resident::create([
        'village_id'  => $village->id,
        'nik'         => '7777777777777777',
        'name'        => 'Wanita Test',
        'birth_place' => 'Singaraja',
        'birth_date'  => '1990-01-01',
        'gender'      => 'P',
    ]);

    $passed = true;
    (new PmksAgeRule($resident->id, $category->id))
        ->validate('category_id', $category->id, function () use (&$passed) {
            $passed = false;
        });

    expect($passed)->toBeTrue();
});

it('penduduk laki-laki tidak bisa masuk PMKS-23', function () {
    [$village, $user, $batch] = createAgeTestSetup();

    $category = PmksCategory::firstOrCreate(
        ['code' => 'PMKS-23'],
        ['name' => 'Fakir Miskin', 'gender_restriction' => 'P']
    );
    $resident = Resident::create([
        'village_id'  => $village->id,
        'nik'         => '8888888888888888',
        'name'        => 'Pria Test',
        'birth_place' => 'Singaraja',
        'birth_date'  => '1990-01-01',
        'gender'      => 'L',
    ]);

    $passed = true;
    (new PmksAgeRule($resident->id, $category->id))
        ->validate('category_id', $category->id, function () use (&$passed) {
            $passed = false;
        });

    expect($passed)->toBeFalse();
});
