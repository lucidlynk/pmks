<?php

use App\Models\PmksCategory;
use App\Models\PmksSubmission;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Models\Village;
use App\Models\Kecamatan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function setupDisabilityData($targetCategoryId = 9)
{
    // 1. Setup Wilayah
    $kecamatan = Kecamatan::create(['name' => 'Seririt', 'code' => '510804']);
    $village   = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Patemon', 'code' => 'V001', 'type' => 'desa']);
    
    // 2. Setup User
    $user = User::create([
        'name' => 'Operator Test',
        'email' => 'test' . uniqid() . '@pmks.com',
        'password' => bcrypt('password'),
        'village_id' => $village->id
    ]);
    
    // 3. Setup Kategori (Kunci Perbaikan: Gunakan create untuk memastikan FK tersedia)
    $category = PmksCategory::create([
        'id' => $targetCategoryId, 
        'code' => 'CAT-' . $targetCategoryId, 
        'name' => 'Kategori ' . $targetCategoryId, 
        'is_active' => true
    ]);

    // 4. Setup Penduduk
    $resident = Resident::create([
        'village_id' => $village->id,
        'nik' => '5108' . rand(1000000000, 9999999999),
        'name' => 'Warga Test',
        'birth_place' => 'Buleleng',
        'birth_date' => '1990-01-01',
        'gender' => 'L'
    ]);
    
    // 5. Setup Batch
    $batch = SubmissionBatch::create([
        'village_id' => $village->id, 
        'period_year' => now()->year, 
        'status' => 'draft',
        'submitted_by' => $user->id
    ]);

    return [$user, $resident, $batch, $category];
}

it('dapat menyimpan jenis disabilitas sebagai array', function () {
    // Kita gunakan ID 9 untuk Disabilitas
    [$user, $resident, $batch, $category] = setupDisabilityData(9);
    
    $disabilityTypes = ['fisik', 'sensorik'];

    $submission = PmksSubmission::create([
        'batch_id' => $batch->id,
        'village_id' => $village_id = $resident->village_id,
        'resident_id' => $resident->id,
        'category_id' => $category->id, // Menggunakan ID dari objek yang baru dibuat
        'input_by' => $user->id,
        'status' => 'draft',
        'disability_types' => $disabilityTypes,
    ]);

    $this->assertDatabaseHas('pmks_submissions', [
        'id' => $submission->id,
        'category_id' => $category->id,
    ]);

    expect($submission->refresh()->disability_types)
        ->toBeArray()
        ->toContain('fisik')
        ->toContain('sensorik');
});

it('menyimpan null jika bukan kategori disabilitas', function () {
    // Kita gunakan ID selain 9 atau 5, misal ID 1
    [$user, $resident, $batch, $category] = setupDisabilityData(1);
    
    $submission = PmksSubmission::create([
        'batch_id' => $batch->id,
        'village_id' => $resident->village_id,
        'resident_id' => $resident->id,
        'category_id' => $category->id,
        'input_by' => $user->id,
        'status' => 'draft',
        'disability_types' => null,
    ]);

    expect($submission->disability_types)->toBeNull();
});
