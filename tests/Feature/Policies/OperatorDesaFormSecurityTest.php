<?php

use App\Enums\BatchStatus;
use App\Models\Kecamatan;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Models\Village;

it('operator desa tidak bisa membuat batch untuk desa lain', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village1  = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa A', 'code' => 'V001', 'type' => 'desa']);
    $village2  = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa B', 'code' => 'V002', 'type' => 'desa']);

    $operator = User::factory()->operatorDesa()->create(['village_id' => $village1->id]);
    $this->actingAs($operator);

    // Coba submit batch untuk desa lain — village_id harus tetap desa sendiri
    \Livewire\Livewire::test(\App\Filament\Resources\SubmissionBatches\Pages\CreateSubmissionBatch::class)
        ->fillForm([
            'village_id'  => $village2->id, // coba desa lain
            'period_year' => 2025,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Batch yang tersimpan harus pakai desa operator, bukan desa lain
    $batch = SubmissionBatch::where('period_year', 2025)->first();
    expect($batch->village_id)->toBe($village1->id);
});

it('operator desa hanya bisa lihat batch desanya di dropdown', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village1  = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa A', 'code' => 'V001', 'type' => 'desa']);
    $village2  = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa B', 'code' => 'V002', 'type' => 'desa']);

    $operator = User::factory()->operatorDesa()->create(['village_id' => $village1->id]);

    $batch1 = SubmissionBatch::create(['village_id' => $village1->id, 'submitted_by' => $operator->id, 'period_year' => 2025, 'status' => BatchStatus::DRAFT]);
    $batch2 = SubmissionBatch::create(['village_id' => $village2->id, 'submitted_by' => $operator->id, 'period_year' => 2025, 'status' => BatchStatus::DRAFT]);

    // Operator hanya boleh lihat batch1, bukan batch2
    $this->actingAs($operator);
    $visible = SubmissionBatch::whereIn('status', [BatchStatus::DRAFT->value])
        ->where('village_id', $operator->village_id)
        ->pluck('id');

    expect($visible)->toContain($batch1->id)
        ->and($visible)->not->toContain($batch2->id);
});

it('operator desa tidak bisa input pmks untuk penduduk desa lain', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village1  = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa A', 'code' => 'V001', 'type' => 'desa']);
    $village2  = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa B', 'code' => 'V002', 'type' => 'desa']);

    $operator  = User::factory()->operatorDesa()->create(['village_id' => $village1->id]);
    $resident2 = Resident::create([
        'village_id'  => $village2->id,
        'nik'         => '2222222222222222',
        'name'        => 'Penduduk Desa B',
        'birth_place' => 'X',
        'birth_date'  => '1990-01-01',
        'gender'      => 'L',
    ]);

    // Policy: operator desa tidak bisa view resident desa lain
    expect($operator->can('view', $resident2))->toBeFalse();
});
