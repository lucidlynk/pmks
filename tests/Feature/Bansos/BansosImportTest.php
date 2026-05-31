<?php

use App\Jobs\Bansos\BansosChunkJob;
use App\Models\BansosImport;
use App\Models\BansosMember;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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

it('operator desa tidak bisa akses halaman import bansos', function () {
    $user = User::factory()->operatorDesa()->create();
    $this->actingAs($user)
         ->get('/admin/bansos-imports')
         ->assertForbidden();
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

it('hanya admin dinsos yang bisa download bansos', function () {
    $admin = User::factory()->adminDinsos()->create();
    $op    = User::factory()->operatorBidang()->create();

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
        ->and($op->can('download', $import))->toBeFalse();
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
