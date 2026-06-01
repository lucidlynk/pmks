<?php

use App\Enums\BatchStatus;
use App\Models\ApiClient;
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
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Models\Village;
use Laravel\Sanctum\PersonalAccessToken;

// ================================================================
// HELPERS
// ================================================================

function createAuthorizedToken(): array
{
    $user        = User::factory()->adminDinsos()->create();
    $plainToken  = $user->createToken('test-kominfo')->plainTextToken;
    $accessToken = PersonalAccessToken::findToken($plainToken);

    $apiClient = ApiClient::create([
        'nama_instansi' => 'Kominfo Test',
        'token_id'      => $accessToken->id,
        'token_preview' => substr($plainToken, strpos($plainToken, '|') + 1, 8),
        'is_active'     => true,
        'created_by'    => $user->id,
    ]);

    return [$plainToken, $apiClient, $user];
}

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

// ================================================================
// AUTH MIDDLEWARE
// ================================================================

it('api ditolak tanpa token', function () {
    $this->getJson('/api/v1/statistik/ringkasan')
        ->assertUnauthorized()
        ->assertJsonPath('success', false);
});

it('api ditolak dengan token tidak valid', function () {
    $this->withToken('token-tidak-valid')
        ->getJson('/api/v1/statistik/ringkasan')
        ->assertUnauthorized()
        ->assertJsonPath('success', false);
});

it('api ditolak dengan token nonaktif', function () {
    [$token, $apiClient] = createAuthorizedToken();
    $apiClient->update(['is_active' => false]);

    $this->withToken($token)
        ->getJson('/api/v1/statistik/ringkasan')
        ->assertForbidden()
        ->assertJsonPath('success', false);
});

it('api berhasil dengan token valid', function () {
    [$token] = createAuthorizedToken();

    $this->withToken($token)
        ->getJson('/api/v1/statistik/ringkasan')
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('log akses dicatat saat request berhasil', function () {
    [$token, $apiClient] = createAuthorizedToken();

    $this->withToken($token)
        ->getJson('/api/v1/statistik/ringkasan?tahun=' . now()->year)
        ->assertOk();

    expect(\App\Models\ApiTokenLog::where('api_client_id', $apiClient->id)->exists())->toBeTrue();

    $log = \App\Models\ApiTokenLog::where('api_client_id', $apiClient->id)->first();
    expect($log->response_code)->toBe(200);
});

it('last_used_at diperbarui setelah request', function () {
    [$token, $apiClient] = createAuthorizedToken();
    expect($apiClient->last_used_at)->toBeNull();

    $this->withToken($token)->getJson('/api/v1/statistik/ringkasan')->assertOk();

    $apiClient->refresh();
    expect($apiClient->last_used_at)->not->toBeNull();
});

// ================================================================
// ENDPOINT: RINGKASAN
// ================================================================

it('api ringkasan mengembalikan data yang benar', function () {
    [$token] = createAuthorizedToken();
    createApiSetup();

    $this->withToken($token)
        ->getJson('/api/v1/statistik/ringkasan?tahun=' . now()->year)
        ->assertOk()
        ->assertJsonStructure([
            'success', 'tahun', 'wilayah',
            'data' => ['total_pmks', 'total_psks', 'total_kecamatan', 'total_desa'],
            'generated_at',
        ])
        ->assertJsonPath('data.total_pmks', 1)
        ->assertJsonPath('data.total_psks', 1);
});

// ================================================================
// ENDPOINT: PMKS & PSKS
// ================================================================

it('api pmks mengembalikan struktur per kecamatan', function () {
    [$token] = createAuthorizedToken();
    createApiSetup();

    $this->withToken($token)
        ->getJson('/api/v1/statistik/pmks?tahun=' . now()->year)
        ->assertOk()
        ->assertJsonStructure([
            'success', 'tahun', 'total_pmks',
            'data' => ['*' => ['id', 'nama_kecamatan', 'total_pmks', 'desa']],
        ])
        ->assertJsonPath('total_pmks', 1);
});

it('api psks mengembalikan struktur per kecamatan', function () {
    [$token] = createAuthorizedToken();
    createApiSetup();

    $this->withToken($token)
        ->getJson('/api/v1/statistik/psks?tahun=' . now()->year)
        ->assertOk()
        ->assertJsonPath('total_psks', 1);
});

it('api per kecamatan mengembalikan ringkasan pmks dan psks', function () {
    [$token] = createAuthorizedToken();
    createApiSetup();

    $this->withToken($token)
        ->getJson('/api/v1/statistik/kecamatan?tahun=' . now()->year)
        ->assertOk()
        ->assertJsonStructure([
            'success', 'tahun', 'total_pmks', 'total_psks',
            'data' => ['*' => ['id', 'nama_kecamatan', 'kode', 'total_desa', 'total_pmks', 'total_psks']],
        ]);
});

it('api per desa mengembalikan data desa', function () {
    [$token] = createAuthorizedToken();
    [$kecamatan] = createApiSetup();

    $this->withToken($token)
        ->getJson('/api/v1/statistik/desa/' . $kecamatan->id . '?tahun=' . now()->year)
        ->assertOk()
        ->assertJsonPath('total_pmks', 1)
        ->assertJsonPath('total_psks', 1);
});

it('api mendukung filter tahun', function () {
    [$token] = createAuthorizedToken();
    createApiSetup();

    $this->withToken($token)
        ->getJson('/api/v1/statistik/ringkasan?tahun=2020')
        ->assertOk()
        ->assertJsonPath('tahun', 2020)
        ->assertJsonPath('data.total_pmks', 0);
});

// ================================================================
// ENDPOINT: DTSEN
// ================================================================

it('api dtsen mengembalikan null jika belum ada data', function () {
    [$token] = createAuthorizedToken();

    $this->withToken($token)
        ->getJson('/api/v1/statistik/dtsen')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data', null);
});

it('api dtsen mengembalikan data rekap terbaru', function () {
    [$token] = createAuthorizedToken();
    $user = User::factory()->adminDinsos()->create();

    $rekap = DtsenRekap::create([
        'bulan'             => 5,
        'tahun'             => now()->year,
        'file_path'         => 'test/dummy.xlsx',
        'original_filename' => 'dummy.xlsx',
        'uploaded_by'       => $user->id,
    ]);
    DtsenRekapDetail::create([
        'dtsen_rekap_id'  => $rekap->id,
        'kecamatan'       => 'Buleleng',
        'kelurahan'       => 'Banyuning',
        'jumlah_keluarga' => 100,
        'jumlah_individu' => 300,
    ]);

    $this->withToken($token)
        ->getJson('/api/v1/statistik/dtsen')
        ->assertOk()
        ->assertJsonPath('data.total_keluarga', 100)
        ->assertJsonPath('data.total_jiwa', 300);
});

// ================================================================
// ENDPOINT: KIS
// ================================================================

it('api kis mengembalikan array kosong jika belum ada rekap', function () {
    [$token] = createAuthorizedToken();

    $this->withToken($token)
        ->getJson('/api/v1/statistik/kis?tahun=' . now()->year)
        ->assertOk()
        ->assertJsonPath('data', []);
});

it('api kis mengembalikan data per bulan', function () {
    [$token] = createAuthorizedToken();
    $user = User::factory()->adminDinsos()->create();

    KisRekap::create([
        'periode_bulan' => 3,
        'periode_tahun' => now()->year,
        'pbi_apbd' => 1000, 'pbi_apbn' => 2000, 'ppu' => 500,
        'pbpu' => 300, 'bp' => 100, 'total' => 3900,
        'created_by' => $user->id,
    ]);

    $this->withToken($token)
        ->getJson('/api/v1/statistik/kis?tahun=' . now()->year)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.bulan', 3)
        ->assertJsonPath('data.0.nama_bulan', 'Maret')
        ->assertJsonPath('data.0.total', 3900);
});

it('api kis mendukung filter per bulan', function () {
    [$token] = createAuthorizedToken();
    $user = User::factory()->adminDinsos()->create();

    foreach ([1, 3, 5] as $bulan) {
        KisRekap::create([
            'periode_bulan' => $bulan, 'periode_tahun' => now()->year,
            'pbi_apbd' => 100, 'pbi_apbn' => 200, 'ppu' => 50,
            'pbpu' => 30, 'bp' => 10, 'total' => 390,
            'created_by' => $user->id,
        ]);
    }

    $this->withToken($token)
        ->getJson('/api/v1/statistik/kis?tahun=' . now()->year . '&bulan=3')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.bulan', 3);
});

// ================================================================
// ENDPOINT: BANSOS
// ================================================================

it('api bansos mengembalikan array kosong jika belum ada data', function () {
    [$token] = createAuthorizedToken();

    $this->withToken($token)
        ->getJson('/api/v1/statistik/bansos?jenis=pkh&triwulan=1&tahun=' . now()->year)
        ->assertOk()
        ->assertJsonPath('data', []);
});

it('api bansos mengembalikan agregat per kecamatan', function () {
    [$token] = createAuthorizedToken();
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

    $this->withToken($token)
        ->getJson('/api/v1/statistik/bansos?jenis=pkh&triwulan=1&tahun=' . now()->year)
        ->assertOk()
        ->assertJsonPath('jenis', 'PKH')
        ->assertJsonPath('total_sudah_si', 1)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.nama_kecamatan', 'Buleleng');
});

it('api bansos menolak jenis tidak valid', function () {
    [$token] = createAuthorizedToken();

    $this->withToken($token)
        ->getJson('/api/v1/statistik/bansos?jenis=invalid&triwulan=1&tahun=' . now()->year)
        ->assertStatus(422);
});

it('api bansos menolak triwulan tidak valid', function () {
    [$token] = createAuthorizedToken();

    $this->withToken($token)
        ->getJson('/api/v1/statistik/bansos?jenis=pkh&triwulan=5&tahun=' . now()->year)
        ->assertStatus(422);
});
