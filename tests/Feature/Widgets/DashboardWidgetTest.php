<?php

use App\Enums\BatchStatus;
use App\Filament\Widgets\BatchPerKecamatanWidget;
use App\Filament\Widgets\PmksPsksStatsWidget;
use App\Models\Kecamatan;
use App\Models\PmksCategory;
use App\Models\PmksSubmission;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Models\Village;

it('admin dapat melihat stats widget', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    \Livewire\Livewire::test(PmksPsksStatsWidget::class)
        ->assertSuccessful();
});

it('stats widget menampilkan data tahun ini', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa']);
    $user      = User::factory()->adminDinsos()->create();
    $batch     = SubmissionBatch::create(['village_id' => $village->id, 'submitted_by' => $user->id, 'period_year' => now()->year, 'status' => BatchStatus::APPROVED]);
    $resident  = Resident::create(['village_id' => $village->id, 'nik' => '5108011234567890', 'name' => 'Test', 'birth_place' => 'X', 'birth_date' => '1990-01-01', 'gender' => 'L']);
    $category  = PmksCategory::firstOrCreate(['code' => 'PMKS-23'], ['name' => 'Fakir Miskin']);

    PmksSubmission::create(['batch_id' => $batch->id, 'village_id' => $village->id, 'resident_id' => $resident->id, 'category_id' => $category->id, 'input_by' => $user->id, 'status' => 'approved']);

    $this->actingAs($user);

    \Livewire\Livewire::test(PmksPsksStatsWidget::class)
        ->assertSuccessful();

    expect(PmksSubmission::whereHas('batch', fn ($q) =>
        $q->where('period_year', now()->year)
    )->count())->toBe(1);
});

it('batch per kecamatan widget hanya tampil untuk admin dan operator bidang', function () {
    $admin    = User::factory()->adminDinsos()->create();
    $operator = User::factory()->operatorBidang()->create();
    $desa     = User::factory()->operatorDesa()->create();
    $verif    = User::factory()->verifikator()->create();

    $this->actingAs($admin);
    expect(BatchPerKecamatanWidget::canView())->toBeTrue();

    $this->actingAs($operator);
    expect(BatchPerKecamatanWidget::canView())->toBeTrue();

    $this->actingAs($desa);
    expect(BatchPerKecamatanWidget::canView())->toBeFalse();

    $this->actingAs($verif);
    expect(BatchPerKecamatanWidget::canView())->toBeFalse();
});

it('operator desa hanya melihat statistik desanya sendiri', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village1  = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa A', 'code' => 'V001', 'type' => 'desa']);
    $village2  = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa B', 'code' => 'V002', 'type' => 'desa']);

    $operator = User::factory()->operatorDesa()->create(['village_id' => $village1->id]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $batch1 = SubmissionBatch::create(['village_id' => $village1->id, 'submitted_by' => $user1->id, 'period_year' => now()->year, 'status' => BatchStatus::APPROVED]);
    $batch2 = SubmissionBatch::create(['village_id' => $village2->id, 'submitted_by' => $user2->id, 'period_year' => now()->year, 'status' => BatchStatus::APPROVED]);

    $resident1 = Resident::create(['village_id' => $village1->id, 'nik' => '1111111111111111', 'name' => 'A', 'birth_place' => 'X', 'birth_date' => '1990-01-01', 'gender' => 'L']);
    $resident2 = Resident::create(['village_id' => $village2->id, 'nik' => '2222222222222222', 'name' => 'B', 'birth_place' => 'X', 'birth_date' => '1990-01-01', 'gender' => 'L']);
    $category  = PmksCategory::firstOrCreate(['code' => 'PMKS-23'], ['name' => 'Fakir Miskin']);

    PmksSubmission::create(['batch_id' => $batch1->id, 'village_id' => $village1->id, 'resident_id' => $resident1->id, 'category_id' => $category->id, 'input_by' => $user1->id, 'status' => 'approved']);
    PmksSubmission::create(['batch_id' => $batch2->id, 'village_id' => $village2->id, 'resident_id' => $resident2->id, 'category_id' => $category->id, 'input_by' => $user2->id, 'status' => 'approved']);

    $this->actingAs($operator);

    // Operator desa hanya bisa lihat data desa1
    $count = PmksSubmission::query()
        ->where('village_id', $operator->village_id)
        ->whereHas('batch', fn ($q) => $q->where('period_year', now()->year))
        ->count();

    expect($count)->toBe(1);
});
