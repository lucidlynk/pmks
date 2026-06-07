<?php

use App\Models\BansosMember;
use App\Models\BansosImport;
use App\Models\DtsenRekap;
use App\Models\DtsenRekapDetail;
use App\Models\Kecamatan;
use App\Models\KisRekap;
use App\Models\PmksCategory;
use App\Models\PmksSubmission;
use App\Models\PsksCategory;
use App\Models\PsksSubmission;
use App\Models\SubmissionBatch;
use App\Models\Resident;
use App\Models\User;
use App\Models\Village;
use App\Enums\BatchStatus;

it('halaman utama dapat diakses tanpa login', function () {
    $this->get('/')->assertOk();
});

it('halaman utama menampilkan nama sistem', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Dinas Sosial Kabupaten Buleleng');
});

it('halaman utama menampilkan kategori pmks', function () {
    PmksCategory::firstOrCreate(['code' => 'PMKS-01'], ['name' => 'Anak Balita Terlantar']);

    $this->get('/')->assertSee('PMKS-01');
});

it('halaman utama menampilkan kategori psks', function () {
    PsksCategory::firstOrCreate(['code' => 'PSKS-J-01'], ['name' => 'PSM', 'subject_type' => 'person']);

    $this->get('/')->assertSee('PSKS-J-01');
});

it('halaman utama menampilkan total pmks dan psks tahun ini', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa']);
    $user      = User::factory()->adminDinsos()->create();
    $batch     = SubmissionBatch::create(['village_id' => $village->id, 'submitted_by' => $user->id, 'period_year' => now()->year, 'status' => BatchStatus::APPROVED]);
    $resident  = Resident::create(['village_id' => $village->id, 'nik' => '5108011234567890', 'name' => 'Test', 'birth_place' => 'X', 'birth_date' => '1990-01-01', 'gender' => 'L']);
    $pmksCat   = PmksCategory::firstOrCreate(['code' => 'PMKS-24'], ['name' => 'Fakir Miskin']);
    $psksCat   = PsksCategory::firstOrCreate(['code' => 'PSKS-J-01'], ['name' => 'PSM', 'subject_type' => 'person']);

    PmksSubmission::create(['batch_id' => $batch->id, 'village_id' => $village->id, 'resident_id' => $resident->id, 'category_id' => $pmksCat->id, 'input_by' => $user->id]);
    PsksSubmission::create(['batch_id' => $batch->id, 'village_id' => $village->id, 'category_id' => $psksCat->id, 'subject_type' => 'person', 'subject_id' => $resident->id, 'input_by' => $user->id]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Total PMKS ' . now()->year)
        ->assertSee('Total PSKS ' . now()->year);
});

it('halaman utama menampilkan tabel pmks per kecamatan', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create(['kecamatan_id' => $kecamatan->id, 'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa']);
    $user      = User::factory()->adminDinsos()->create();
    $batch     = SubmissionBatch::create(['village_id' => $village->id, 'submitted_by' => $user->id, 'period_year' => now()->year, 'status' => BatchStatus::APPROVED]);
    $resident  = Resident::create(['village_id' => $village->id, 'nik' => '5108011234567890', 'name' => 'Test', 'birth_place' => 'X', 'birth_date' => '1990-01-01', 'gender' => 'L']);
    $pmksCat   = PmksCategory::firstOrCreate(['code' => 'PMKS-24'], ['name' => 'Fakir Miskin']);

    PmksSubmission::create(['batch_id' => $batch->id, 'village_id' => $village->id, 'resident_id' => $resident->id, 'category_id' => $pmksCat->id, 'input_by' => $user->id]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Rekap per Kecamatan')
        ->assertSee('Buleleng');
});

it('halaman utama menampilkan data kis jika ada', function () {
    $user = User::factory()->adminDinsos()->create();

    KisRekap::create([
        'periode_bulan' => 5, 'periode_tahun' => now()->year,
        'pbi_apbd' => 1000, 'pbi_apbn' => 2000, 'ppu' => 500,
        'pbpu' => 300, 'bp' => 100, 'total' => 3900,
        'created_by' => $user->id,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Kepesertaan JKN/KIS')
        ->assertSee('PBI APBD')
        ->assertSee('3,900', false);
});

it('halaman utama menampilkan data dtsen per desil jika ada', function () {
    $user = User::factory()->adminDinsos()->create();

    $rekap = DtsenRekap::create([
        'bulan' => 5, 'tahun' => now()->year,
        'file_path' => 'test/dummy.xlsx',
        'original_filename' => 'dummy.xlsx',
        'uploaded_by' => $user->id,
    ]);

    DtsenRekapDetail::create([
        'dtsen_rekap_id'  => $rekap->id,
        'kecamatan'       => 'Buleleng',
        'kelurahan'       => 'Banyuning',
        'jumlah_keluarga' => 100,
        'jumlah_individu' => 300,
        'desil1_keluarga' => 10, 'desil1_individu' => 30,
        'desil2_keluarga' => 20, 'desil2_individu' => 60,
        'desil3_keluarga' => 25, 'desil3_individu' => 75,
        'desil4_keluarga' => 22, 'desil4_individu' => 66,
        'desil5_keluarga' => 23, 'desil5_individu' => 69,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('DTSEN')
        ->assertSee('Desil 1 (Termiskin)')
        ->assertSee('Desil 5');
});

it('halaman utama menampilkan data bansos jika ada', function () {
    $user = User::factory()->adminDinsos()->create();

    $import = BansosImport::create([
        'jenis_bansos' => 'pkh', 'status_bansos' => 'sudah_si',
        'triwulan' => 1, 'tahun' => now()->year,
        'original_filename' => 'test.csv', 'file_path' => 'test.csv',
        'status' => 'done', 'uploaded_by' => $user->id,
    ]);

    BansosMember::create([
        'import_id' => $import->id, 'nama_penerima' => 'Putu Test',
        'nik' => '510805****', 'nokk' => '5108****',
        'jenis_bansos' => 'pkh', 'kec_name' => 'Buleleng',
        'kel_name' => 'Banyuning', 'status_bansos' => 'sudah_si',
        'triwulan' => 1, 'tahun' => now()->year,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Realisasi Bansos')
        ->assertSee('PKH');
});

it('halaman utama tidak error jika belum ada data kis dtsen bansos', function () {
    $this->get('/')->assertOk();
});
