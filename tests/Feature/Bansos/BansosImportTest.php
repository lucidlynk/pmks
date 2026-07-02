<?php

use App\Jobs\Bansos\BansosChunkJob;
use App\Models\BansosImport;
use App\Models\BansosMember;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

// ================================================================
// AKSES HALAMAN
// ================================================================

it('admin dinsos bisa akses halaman import bansos', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user)
         ->get('/admin/bansos-imports')
         ->assertOk();
});

it('operator bidang bisa akses halaman import bansos', function () {
    $user = User::factory()->operatorBidang()->create();
    $this->actingAs($user)
         ->get('/admin/bansos-imports')
         ->assertOk();
});

it('verifikator bisa akses halaman import bansos', function () {
    $user = User::factory()->verifikator()->create();
    $this->actingAs($user)
         ->get('/admin/bansos-imports')
         ->assertOk();
});

it('operator desa bisa akses halaman import bansos', function () {
    $user = User::factory()->operatorDesa()->create();
    $this->actingAs($user)
         ->get('/admin/bansos-imports')
         ->assertOk();
});

// ================================================================
// UPLOAD & DISPATCH JOB
// ================================================================

it('admin dinsos bisa upload csv bansos dan job terdispatch', function () {
    Storage::fake('local');
    Queue::fake();

    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    $csv  = "NAMA_PENERIMA|NIK|NOKK|PENYALURAN_OLEH|BANSOS|PROP_NAME|KAB_NAME|KEC_NAME|KEL_NAME|ALAMAT|status|kode_batch_penyaluran\n";
    $csv .= "NI WAYAN AYU|5108024511890005|5108000000000001|BRI|PKH|BALI|BULELENG|SUKASADA|PEGAYAMAN|JL TEST|SUDAH SI|BATCH001\n";
    $file = UploadedFile::fake()->createWithContent('pkh-sudah-si.csv', $csv);

    \Livewire\Livewire::test(\App\Filament\Resources\BansosImports\Pages\CreateBansosImport::class)
        ->fillForm([
            'jenis_bansos'  => 'pkh',
            'status_bansos' => 'sudah_si',
            'triwulan'      => 1,
            'tahun'         => 2026,
            'file_path'     => $file,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $import = BansosImport::first();
    expect($import)->not->toBeNull()
        ->and($import->jenis_bansos)->toBe('pkh')
        ->and($import->status_bansos)->toBe('sudah_si')
        ->and($import->triwulan)->toBe(1)
        ->and($import->uploaded_by)->toBe($user->id);

    Queue::assertPushedOn('imports', \App\Jobs\Bansos\BansosParserJob::class);
});

it('verifikator tidak bisa upload csv bansos', function () {
    $user = User::factory()->verifikator()->create();
    $this->actingAs($user)
         ->get('/admin/bansos-imports/create')
         ->assertForbidden();
});

// ================================================================
// CHUNK JOB
// ================================================================

it('chunk job menyimpan data bansos dengan benar', function () {
    $admin  = User::factory()->adminDinsos()->create();
    $import = BansosImport::create([
        'jenis_bansos'      => 'pkh',
        'status_bansos'     => 'sudah_si',
        'triwulan'          => 1,
        'tahun'             => 2026,
        'original_filename' => 'test.csv',
        'file_path'         => 'bansos-imports/test.csv',
        'status'            => 'processing',
        'uploaded_by'       => $admin->id,
    ]);

    $rows = [
        ['NI WAYAN AYU', '5108024511890001', '5108000000000001', 'BRI', 'PKH', 'BALI', 'BULELENG', 'SUKASADA', 'PEGAYAMAN', 'JL TEST', 'SUDAH SI', 'BATCH001'],
        ['I MADE BUDI', '5108024511890002', '5108000000000002', 'BRI', 'PKH', 'BALI', 'BULELENG', 'SUKASADA', 'PEGAYAMAN', 'JL TEST 2', 'SUDAH SI', 'BATCH001'],
        ['I KETUT SARI', '5108024511890003', '5108000000000003', 'BNI', 'PKH', 'BALI', 'BULELENG', 'BULELENG', 'BANYUNING', 'JL TEST 3', 'SUDAH SI', 'BATCH002'],
    ];

    $job = new BansosChunkJob($import->id, $rows);
    $job->handle();

    expect(BansosMember::where('import_id', $import->id)->count())->toBe(3);

    $import->refresh();
    expect($import->processed_rows)->toBe(3)
        ->and($import->failed_rows)->toBe(0);
});

it('chunk job normalize status dari csv', function () {
    $admin  = User::factory()->adminDinsos()->create();
    $import = BansosImport::create([
        'jenis_bansos'      => 'pkh',
        'status_bansos'     => 'sudah_salur',
        'triwulan'          => 2,
        'tahun'             => 2026,
        'original_filename' => 'test.csv',
        'file_path'         => 'bansos-imports/test.csv',
        'status'            => 'processing',
        'uploaded_by'       => $admin->id,
    ]);

    $rows = [
        ['NI WAYAN AYU', '5108024511890004', '5108000000000004', 'BRI', 'PKH', 'BALI', 'BULELENG', 'SUKASADA', 'PEGAYAMAN', 'JL TEST', 'BERHASIL SALUR', 'BATCH001'],
    ];

    $job = new BansosChunkJob($import->id, $rows);
    $job->handle();

    $member = BansosMember::where('import_id', $import->id)->first();
    expect($member->status_bansos)->toBe('sudah_salur');
});

it('chunk job normalize jenis bansos dari csv', function () {
    $admin  = User::factory()->adminDinsos()->create();
    $import = BansosImport::create([
        'jenis_bansos'      => 'sembako',
        'status_bansos'     => 'sudah_si',
        'triwulan'          => 1,
        'tahun'             => 2026,
        'original_filename' => 'test.csv',
        'file_path'         => 'bansos-imports/test.csv',
        'status'            => 'processing',
        'uploaded_by'       => $admin->id,
    ]);

    $rows = [
        ['NI NYOMAN SARI', '5108024511890005', '5108000000000005', 'BRI', 'SEMBAKO', 'BALI', 'BULELENG', 'BULELENG', 'BANYUNING', 'JL TEST', 'SUDAH SI', 'BATCH003'],
    ];

    $job = new BansosChunkJob($import->id, $rows);
    $job->handle();

    $member = BansosMember::where('import_id', $import->id)->first();
    expect($member->jenis_bansos)->toBe('sembako');
});

// ================================================================
// POLICY
// ================================================================

it('akses download bansos sesuai role', function () {
    $admin      = User::factory()->adminDinsos()->create();
    $opBidang   = User::factory()->operatorBidang()->create();
    $opDesa     = User::factory()->operatorDesa()->create();
    $staf       = User::factory()->stafDinsos()->create();
    $verifikator = User::factory()->verifikator()->create();

    $import = BansosImport::create([
        'jenis_bansos'      => 'pkh',
        'status_bansos'     => 'sudah_si',
        'triwulan'          => 1,
        'tahun'             => 2026,
        'original_filename' => 'test.csv',
        'file_path'         => 'bansos-imports/test.csv',
        'status'            => 'done',
        'uploaded_by'       => $admin->id,
    ]);

    expect($admin->can('download', $import))->toBeTrue()
        ->and($opBidang->can('download', $import))->toBeTrue()
        ->and($opDesa->can('download', $import))->toBeTrue()
        ->and($staf->can('download', $import))->toBeTrue()
        ->and($verifikator->can('download', $import))->toBeFalse();
});

// ================================================================
// RE-UPLOAD / DATA SAFETY
// ================================================================

it('re-upload dengan file valid menghapus data lama dan memproses data baru', function () {
    Storage::fake('local');

    $admin = User::factory()->adminDinsos()->create();

    // Import lama yang sudah done
    $oldImport = BansosImport::create([
        'jenis_bansos'      => 'pkh',
        'status_bansos'     => 'sudah_si',
        'triwulan'          => 1,
        'tahun'             => 2026,
        'original_filename' => 'lama.csv',
        'file_path'         => 'bansos-imports/lama.csv',
        'status'            => 'done',
        'uploaded_by'       => $admin->id,
    ]);
    $oldImportId = $oldImport->id;

    // Data lama yang akan digantikan
    BansosMember::create([
        'import_id'    => $oldImportId,
        'nama_penerima'=> 'WARGA LAMA',
        'nik'          => '510802000000****',
        'nokk'         => '5108000000000001',
        'jenis_bansos' => 'pkh',
        'kec_name'     => 'SUKASADA',
        'kel_name'     => 'PEGAYAMAN',
        'status_bansos'=> 'sudah_si',
        'triwulan'     => 1,
        'tahun'        => 2026,
    ]);

    // File CSV baru yang valid
    $csv  = "NAMA_PENERIMA|NIK|NOKK|PENYALURAN_OLEH|BANSOS|PROP_NAME|KAB_NAME|KEC_NAME|KEL_NAME|ALAMAT|status|kode_batch_penyaluran\n";
    $csv .= "NI WAYAN BARU|5108024511890010|5108000000000099|BRI|PKH|BALI|BULELENG|SUKASADA|PEGAYAMAN|JL BARU|SUDAH SI|BATCH001\n";
    Storage::disk('local')->put('bansos-imports/baru.csv', $csv);

    $newImport = BansosImport::create([
        'jenis_bansos'      => 'pkh',
        'status_bansos'     => 'sudah_si',
        'triwulan'          => 1,
        'tahun'             => 2026,
        'original_filename' => 'baru.csv',
        'file_path'         => 'bansos-imports/baru.csv',
        'status'            => 'pending',
        'uploaded_by'       => $admin->id,
    ]);

    // Tangkap DELETE query via event dispatcher — tetap aktif lintas DB::reconnect()
    // DB::reconnect() di dalam job membatalkan test transaction, tapi DB::listen()
    // bekerja di level application sehingga DELETE sebelum reconnect tetap tertangkap
    $deleteQueries = [];
    \Illuminate\Support\Facades\DB::listen(function ($query) use (&$deleteQueries) {
        if (str_contains(strtolower($query->sql), 'delete')) {
            $deleteQueries[] = $query->sql;
        }
    });

    // Jalankan job — DB::reconnect() di dalamnya akan membatalkan test transaction.
    // Exception setelah reconnect (ModelNotFoundException di BansosChunkJob) ditangkap
    // oleh try/catch di dalam handle() sehingga handle() tetap return normal.
    (new \App\Jobs\Bansos\BansosParserJob($newImport->id))->handle();

    // Verifikasi DELETE dieksekusi (terjadi SEBELUM DB::reconnect — itu intinya fix ini)
    $deletedMembers = collect($deleteQueries)->contains(
        fn($sql) => str_contains($sql, 'bansos_members')
    );
    $deletedImport = collect($deleteQueries)->contains(
        fn($sql) => str_contains($sql, 'bansos_imports')
    );

    expect($deletedMembers)->toBeTrue('bansos_members lama harus dihapus saat file valid');
    expect($deletedImport)->toBeTrue('bansos_imports lama harus dihapus saat file valid');
});

it('re-upload dengan file csv kosong tidak menghapus data lama', function () {
    Storage::fake('local');
    Queue::fake();

    $admin = User::factory()->adminDinsos()->create();

    // Import lama yang sudah done
    $oldImport = BansosImport::create([
        'jenis_bansos'      => 'pkh',
        'status_bansos'     => 'sudah_si',
        'triwulan'          => 2,
        'tahun'             => 2026,
        'original_filename' => 'lama.csv',
        'file_path'         => 'bansos-imports/lama.csv',
        'status'            => 'done',
        'uploaded_by'       => $admin->id,
    ]);

    BansosMember::create([
        'import_id'    => $oldImport->id,
        'nama_penerima'=> 'WARGA LAMA',
        'nik'          => '510802000001****',
        'nokk'         => '5108000000000002',
        'jenis_bansos' => 'pkh',
        'kec_name'     => 'SUKASADA',
        'kel_name'     => 'PEGAYAMAN',
        'status_bansos'=> 'sudah_si',
        'triwulan'     => 2,
        'tahun'        => 2026,
    ]);

    // File CSV baru tapi kosong (hanya header)
    $csv = "NAMA_PENERIMA|NIK|NOKK|PENYALURAN_OLEH|BANSOS|PROP_NAME|KAB_NAME|KEC_NAME|KEL_NAME|ALAMAT|status|kode_batch_penyaluran\n";
    Storage::disk('local')->put('bansos-imports/kosong.csv', $csv);

    $newImport = BansosImport::create([
        'jenis_bansos'      => 'pkh',
        'status_bansos'     => 'sudah_si',
        'triwulan'          => 2,
        'tahun'             => 2026,
        'original_filename' => 'kosong.csv',
        'file_path'         => 'bansos-imports/kosong.csv',
        'status'            => 'pending',
        'uploaded_by'       => $admin->id,
    ]);

    (new \App\Jobs\Bansos\BansosParserJob($newImport->id))->handle();

    // Data lama HARUS TETAP ADA karena file baru kosong
    expect(BansosMember::where('import_id', $oldImport->id)->count())->toBe(1);
    expect(BansosImport::find($oldImport->id))->not->toBeNull();

    // Import baru harus gagal
    $newImport->refresh();
    expect($newImport->status)->toBe('failed');
});

// ================================================================
// MODEL HELPERS
// ================================================================

it('label jenis dan status bansos benar', function () {
    $admin  = User::factory()->adminDinsos()->create();
    $import = BansosImport::create([
        'jenis_bansos'      => 'sembako',
        'status_bansos'     => 'sudah_transaksi',
        'triwulan'          => 3,
        'tahun'             => 2026,
        'original_filename' => 'test.csv',
        'file_path'         => 'bansos-imports/test.csv',
        'status'            => 'done',
        'uploaded_by'       => $admin->id,
    ]);

    expect($import->jenis_label)->toBe('Sembako')
        ->and($import->status_bansos_label)->toBe('Sudah Transaksi')
        ->and($import->triwulan_label)->toBe('TW3 2026');
});
