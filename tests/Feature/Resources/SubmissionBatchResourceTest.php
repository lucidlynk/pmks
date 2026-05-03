<?php

use App\Enums\BatchStatus;
use App\Models\Kecamatan;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Models\Village;

function createBatchSetup(): array
{
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa',
    ]);
    return [$kecamatan, $village];
}

it('operator desa dapat membuat batch baru', function () {
    [$kecamatan, $village] = createBatchSetup();

    // Operator Desa dengan village_id terisi
    $user = User::factory()->operatorDesa()->create(['village_id' => $village->id]);
    $this->actingAs($user);

    \Livewire\Livewire::test(\App\Filament\Resources\SubmissionBatches\Pages\CreateSubmissionBatch::class)
        ->fillForm([
            'period_year' => 2025,
            // village_id otomatis dari user, tidak perlu diisi
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(SubmissionBatch::where('village_id', $village->id)
        ->where('period_year', 2025)
        ->exists()
    )->toBeTrue();
});

it('tidak bisa membuat 2 batch untuk desa dan tahun yang sama', function () {
    [$kecamatan, $village] = createBatchSetup();
    $user = User::factory()->operatorDesa()->create(['village_id' => $village->id]);

    SubmissionBatch::create([
        'village_id'   => $village->id,
        'submitted_by' => $user->id,
        'period_year'  => 2025,
        'status'       => BatchStatus::DRAFT,
    ]);

    expect(SubmissionBatch::where('village_id', $village->id)
        ->where('period_year', 2025)
        ->count()
    )->toBe(1);
});

it('status awal batch adalah draft', function () {
    [$kecamatan, $village] = createBatchSetup();
    $user = User::factory()->operatorDesa()->create(['village_id' => $village->id]);

    $batch = SubmissionBatch::create([
        'village_id'   => $village->id,
        'submitted_by' => $user->id,
        'period_year'  => 2025,
        'status'       => BatchStatus::DRAFT,
    ]);

    expect($batch->status)->toBe(BatchStatus::DRAFT)
        ->and($batch->isDraft())->toBeTrue();
});

it('batch draft bisa disubmit', function () {
    [$kecamatan, $village] = createBatchSetup();
    $user = User::factory()->operatorDesa()->create(['village_id' => $village->id]);

    $batch = SubmissionBatch::create([
        'village_id'   => $village->id,
        'submitted_by' => $user->id,
        'period_year'  => 2025,
        'status'       => BatchStatus::DRAFT,
    ]);

    $batch->update(['status' => BatchStatus::SUBMITTED]);

    expect($batch->fresh()->status)->toBe(BatchStatus::SUBMITTED)
        ->and($batch->fresh()->isSubmitted())->toBeTrue();
});

it('batch submitted bisa diverifikasi', function () {
    [$kecamatan, $village] = createBatchSetup();
    $verifikator = User::factory()->verifikator()->create();

    $batch = SubmissionBatch::create([
        'village_id'   => $village->id,
        'submitted_by' => $verifikator->id,
        'period_year'  => 2025,
        'status'       => BatchStatus::SUBMITTED,
    ]);

    $batch->update([
        'status'      => BatchStatus::VERIFIED,
        'verified_by' => $verifikator->id,
        'verified_at' => now(),
    ]);

    expect($batch->fresh()->status)->toBe(BatchStatus::VERIFIED);
});

it('batch verified bisa diapprove', function () {
    [$kecamatan, $village] = createBatchSetup();
    $admin = User::factory()->adminDinsos()->create();

    $batch = SubmissionBatch::create([
        'village_id'   => $village->id,
        'submitted_by' => $admin->id,
        'period_year'  => 2025,
        'status'       => BatchStatus::VERIFIED,
    ]);

    $batch->update([
        'status'      => BatchStatus::APPROVED,
        'approved_by' => $admin->id,
        'approved_at' => now(),
    ]);

    expect($batch->fresh()->status)->toBe(BatchStatus::APPROVED)
        ->and($batch->fresh()->isApproved())->toBeTrue();
});

it('batch approved bisa diminta revisi', function () {
    [$kecamatan, $village] = createBatchSetup();
    $admin = User::factory()->adminDinsos()->create();

    $batch = SubmissionBatch::create([
        'village_id'   => $village->id,
        'submitted_by' => $admin->id,
        'period_year'  => 2025,
        'status'       => BatchStatus::APPROVED,
    ]);

    $batch->update(['status' => BatchStatus::REVISION_REQUESTED]);
    $batch->revisions()->create([
        'requested_by' => $admin->id,
        'reason'       => 'Ada data yang salah',
        'status'       => 'pending',
    ]);

    expect($batch->fresh()->status)->toBe(BatchStatus::REVISION_REQUESTED)
        ->and($batch->fresh()->revisions()->count())->toBe(1);
});
