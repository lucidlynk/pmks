<?php

use App\Enums\BatchStatus;
use App\Models\FamilyCard;
use App\Models\Kecamatan;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Models\Village;

function createTwoVillages(): array
{
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village1  = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa A', 'code' => 'V001', 'type' => 'desa']);
    $village2  = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa B', 'code' => 'V002', 'type' => 'desa']);
    return [$village1, $village2];
}

it('operator desa hanya bisa lihat resident desanya sendiri', function () {
    [$village1, $village2] = createTwoVillages();

    $operator = User::factory()->operatorDesa()->create(['village_id' => $village1->id]);

    $resident1 = Resident::create(['village_id' => $village1->id, 'nik' => '1111111111111111', 'name' => 'A', 'birth_place' => 'X', 'birth_date' => '1990-01-01', 'gender' => 'L']);
    $resident2 = Resident::create(['village_id' => $village2->id, 'nik' => '2222222222222222', 'name' => 'B', 'birth_place' => 'X', 'birth_date' => '1990-01-01', 'gender' => 'L']);

    expect($operator->can('view', $resident1))->toBeTrue()
        ->and($operator->can('view', $resident2))->toBeFalse();
});

it('operator desa hanya bisa lihat batch desanya sendiri', function () {
    [$village1, $village2] = createTwoVillages();

    $operator = User::factory()->operatorDesa()->create(['village_id' => $village1->id]);

    $batch1 = SubmissionBatch::create(['village_id' => $village1->id, 'submitted_by' => $operator->id, 'period_year' => 2025, 'status' => BatchStatus::DRAFT]);
    $batch2 = SubmissionBatch::create(['village_id' => $village2->id, 'submitted_by' => $operator->id, 'period_year' => 2025, 'status' => BatchStatus::DRAFT]);

    expect($operator->can('view', $batch1))->toBeTrue()
        ->and($operator->can('view', $batch2))->toBeFalse();
});

it('operator desa hanya bisa edit batch desanya yang masih draft', function () {
    [$village1, $village2] = createTwoVillages();

    $operator = User::factory()->operatorDesa()->create(['village_id' => $village1->id]);

    $batch = SubmissionBatch::create(['village_id' => $village1->id, 'submitted_by' => $operator->id, 'period_year' => 2025, 'status' => BatchStatus::DRAFT]);

    expect($operator->can('update', $batch))->toBeTrue();

    $batch->update(['status' => BatchStatus::APPROVED]);
    expect($operator->can('update', $batch->fresh()))->toBeFalse();
});

it('admin dinsos bisa akses semua data lintas desa', function () {
    [$village1, $village2] = createTwoVillages();

    $admin = User::factory()->adminDinsos()->create();

    $resident1 = Resident::create(['village_id' => $village1->id, 'nik' => '1111111111111111', 'name' => 'A', 'birth_place' => 'X', 'birth_date' => '1990-01-01', 'gender' => 'L']);
    $resident2 = Resident::create(['village_id' => $village2->id, 'nik' => '2222222222222222', 'name' => 'B', 'birth_place' => 'X', 'birth_date' => '1990-01-01', 'gender' => 'L']);

    expect($admin->can('view', $resident1))->toBeTrue()
        ->and($admin->can('view', $resident2))->toBeTrue();
});

it('verifikator bisa lihat semua data tapi tidak bisa edit', function () {
    [$village1, $village2] = createTwoVillages();

    $verifikator = User::factory()->verifikator()->create();

    $resident = Resident::create(['village_id' => $village1->id, 'nik' => '1111111111111111', 'name' => 'A', 'birth_place' => 'X', 'birth_date' => '1990-01-01', 'gender' => 'L']);

    $familyCard = FamilyCard::create(['village_id' => $village1->id, 'no_kk' => '1234567890123456', 'kepala_keluarga' => 'Test', 'address' => 'Jl Test']);

    expect($verifikator->can('view', $resident))->toBeTrue()
        ->and($verifikator->can('update', $resident))->toBeFalse()
        ->and($verifikator->can('view', $familyCard))->toBeTrue()
        ->and($verifikator->can('update', $familyCard))->toBeFalse();
});
