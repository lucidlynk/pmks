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
    $rule = PmksAgeRule::getRulesForCategory('PMKS-01');
    expect($rule['min'])->toBe(0)->and($rule['max'])->toBe(5);
});

it('PMKS-02 sampai PMKS-07 hanya untuk usia 6-18 tahun', function () {
    foreach (['PMKS-02', 'PMKS-03', 'PMKS-04', 'PMKS-05', 'PMKS-06', 'PMKS-07'] as $code) {
        $rule = PmksAgeRule::getRulesForCategory($code);
        expect($rule['min'])->toBe(6)->and($rule['max'])->toBe(18);
    }
});

it('kategori di luar aturan usia tidak ada pembatasan', function () {
    $rule = PmksAgeRule::getRulesForCategory('PMKS-23');
    expect($rule)->toBeNull();
});

it('penduduk usia 3 tahun bisa masuk PMKS-01', function () {
    [$village, $user, $batch] = createAgeTestSetup();

    $category = PmksCategory::create(['code' => 'PMKS-01', 'name' => 'Kemiskinan Balita']);
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
            'batch_id'    => $batch->id,
            'village_id'  => $village->id,
            'resident_id' => $resident->id,
            'category_id' => $category->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(PmksSubmission::where('resident_id', $resident->id)->exists())->toBeTrue();
});

it('penduduk usia 10 tahun tidak bisa masuk PMKS-01', function () {
    [$village, $user, $batch] = createAgeTestSetup();

    $category = PmksCategory::create(['code' => 'PMKS-01', 'name' => 'Kemiskinan Balita']);
    $resident = Resident::create([
        'village_id'  => $village->id,
        'nik'         => '2222222222222222',
        'name'        => 'Anak Test',
        'birth_place' => 'Singaraja',
        'birth_date'  => now()->subYears(10)->format('Y-m-d'),
        'gender'      => 'L',
    ]);

    $passed = true;
    $rule   = new PmksAgeRule($resident->id, $category->id);
    $rule->validate('category_id', $category->id, function () use (&$passed) {
        $passed = false;
    });

    expect($passed)->toBeFalse();
});

it('penduduk usia 15 tahun bisa masuk PMKS-02', function () {
    [$village, $user, $batch] = createAgeTestSetup();

    $category = PmksCategory::create(['code' => 'PMKS-02', 'name' => 'Keterlantaran Anak']);
    $resident = Resident::create([
        'village_id'  => $village->id,
        'nik'         => '3333333333333333',
        'name'        => 'Remaja Test',
        'birth_place' => 'Singaraja',
        'birth_date'  => now()->subYears(15)->format('Y-m-d'),
        'gender'      => 'P',
    ]);

    $passed = true;
    $rule   = new PmksAgeRule($resident->id, $category->id);
    $rule->validate('category_id', $category->id, function () use (&$passed) {
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
            ['name' => "Kategori {$code}"]
        );

        $passed = true;
        $rule   = new PmksAgeRule($resident->id, $category->id);
        $rule->validate('category_id', $category->id, function () use (&$passed) {
            $passed = false;
        });

        expect($passed)->toBeFalse("Kategori {$code} seharusnya ditolak untuk usia 25 tahun");
    }
});
