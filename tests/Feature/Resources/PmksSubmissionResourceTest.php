<?php

use App\Enums\BatchStatus;
use App\Models\Kecamatan;
use App\Models\PmksCategory;
use App\Models\PmksSubmission;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Models\Village;

function createSubmissionSetup(): array
{
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa',
    ]);
    $user = User::factory()->operatorDesa()->create(['village_id' => $village->id]);
    $batch = SubmissionBatch::create([
        'village_id'   => $village->id,
        'submitted_by' => $user->id,
        'period_year'  => 2025,
        'status'       => BatchStatus::DRAFT,
    ]);

    // Gunakan PMKS-24 (Keluarga Bermasalah) — tidak ada aturan usia/gender
    $category = PmksCategory::firstOrCreate(
        ['code' => 'PMKS-24'],
        ['name' => 'Keluarga Bermasalah Sosial Psikologis']
    );

    // Resident laki-laki, usia dewasa — bebas masuk PMKS-24
    $resident = Resident::create([
        'village_id'  => $village->id,
        'nik'         => '5108011234567890',
        'name'        => 'Budi Santoso',
        'birth_place' => 'Singaraja',
        'birth_date'  => '1990-01-15',
        'gender'      => 'L',
    ]);

    return [$kecamatan, $village, $user, $batch, $resident, $category];
}

it('dapat membuat data pmks baru', function () {
    [$kecamatan, $village, $user, $batch, $resident, $category] = createSubmissionSetup();
    $this->actingAs($user);

    \Livewire\Livewire::test(\App\Filament\Resources\PmksSubmissions\Pages\CreatePmksSubmission::class)
        ->fillForm([
            'batch_id'    => $batch->id,
            'village_id'  => $village->id,
            'resident_id' => $resident->id,
            'category_id' => $category->id,
            'notes'       => 'Catatan test',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(PmksSubmission::where('resident_id', $resident->id)
        ->where('category_id', $category->id)
        ->exists()
    )->toBeTrue();
});

it('satu penduduk boleh lebih dari satu kategori pmks dalam satu batch', function () {
    [$kecamatan, $village, $user, $batch, $resident, $category] = createSubmissionSetup();

    $category2 = PmksCategory::firstOrCreate(
        ['code' => 'PMKS-22'],
        ['name' => 'Perempuan Rawan Sosial Ekonomi']
    );

    // Gunakan resident perempuan untuk PMKS-22 jika ada aturan gender
    PmksSubmission::create([
        'batch_id'    => $batch->id,
        'village_id'  => $village->id,
        'resident_id' => $resident->id,
        'category_id' => $category->id,
        'input_by'    => $user->id,
        'status'      => 'draft',
    ]);

    $category3 = PmksCategory::firstOrCreate(
        ['code' => 'PMKS-21'],
        ['name' => 'Korban Bencana Sosial']
    );

    PmksSubmission::create([
        'batch_id'    => $batch->id,
        'village_id'  => $village->id,
        'resident_id' => $resident->id,
        'category_id' => $category3->id,
        'input_by'    => $user->id,
        'status'      => 'draft',
    ]);

    expect(PmksSubmission::where('resident_id', $resident->id)
        ->where('batch_id', $batch->id)
        ->count()
    )->toBe(2);
});

it('satu penduduk tidak boleh kategori yang sama dua kali dalam satu batch', function () {
    [$kecamatan, $village, $user, $batch, $resident, $category] = createSubmissionSetup();

    PmksSubmission::create([
        'batch_id'    => $batch->id,
        'village_id'  => $village->id,
        'resident_id' => $resident->id,
        'category_id' => $category->id,
        'input_by'    => $user->id,
        'status'      => 'draft',
    ]);

    expect(fn () => PmksSubmission::create([
        'batch_id'    => $batch->id,
        'village_id'  => $village->id,
        'resident_id' => $resident->id,
        'category_id' => $category->id,
        'input_by'    => $user->id,
        'status'      => 'draft',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('data pmks tidak bisa diinput jika batch sudah approved', function () {
    [$kecamatan, $village, $user, $batch, $resident, $category] = createSubmissionSetup();

    $batch->update(['status' => BatchStatus::APPROVED]);

    expect($batch->fresh()->canBeEdited())->toBeFalse();
});
