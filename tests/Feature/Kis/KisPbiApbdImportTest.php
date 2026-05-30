<?php

use App\Jobs\Kis\KisPbiApbdChunkJob;
use App\Jobs\Kis\KisPbiApbdParserJob;
use App\Models\KisPbiApbdImport;
use App\Models\KisPbiApbdMember;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

// ================================================================
// AKSES HALAMAN
// ================================================================

it('admin dinsos bisa akses halaman import pbi apbd', function () {
    $user = User::factory()->adminDinsos()->create();

    $this->actingAs($user)
         ->get('/admin/kis-pbi-apbd-imports')
         ->assertOk();
});

it('operator bidang bisa akses halaman import pbi apbd', function () {
    $user = User::factory()->operatorBidang()->create();

    $this->actingAs($user)
         ->get('/admin/kis-pbi-apbd-imports')
         ->assertOk();
});

it('verifikator bisa akses halaman import pbi apbd', function () {
    $user = User::factory()->verifikator()->create();

    $this->actingAs($user)
         ->get('/admin/kis-pbi-apbd-imports')
         ->assertOk();
});

it('operator desa tidak bisa akses halaman import pbi apbd', function () {
    $user = User::factory()->operatorDesa()->create();

    $this->actingAs($user)
         ->get('/admin/kis-pbi-apbd-imports')
         ->assertForbidden();
});

// ================================================================
// UPLOAD & DISPATCH JOB
// ================================================================

it('admin dinsos bisa upload csv dan job terdispatch', function () {
    Storage::fake('local');
    Queue::fake();

    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    $csv = "PSNOKA,NIK,NAMA,SEGMEN,BULAN,TAHUN\n";
    $csv .= "001,1234567890123456,Budi Santoso,PBI APBD,1,2026\n";
    $csv .= "002,1234567890123457,Siti Rahayu,PBI APBD,1,2026\n";

    $file = UploadedFile::fake()->createWithContent('pbi-apbd.csv', $csv);

    \Livewire\Livewire::test(\App\Filament\Resources\KisPbiApbdImports\Pages\CreateKisPbiApbdImport::class)
        ->fillForm([
            'periode_bulan' => 1,
            'periode_tahun' => 2026,
            'file_path'     => $file,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $import = KisPbiApbdImport::first();
    expect($import)->not->toBeNull()
        ->and($import->status)->toBe('pending')
        ->and($import->uploaded_by)->toBe($user->id);

    Queue::assertPushedOn('imports', KisPbiApbdParserJob::class);
});

it('operator bidang bisa upload csv', function () {
    Storage::fake('local');
    Queue::fake();

    $user = User::factory()->operatorBidang()->create();
    $this->actingAs($user);

    $csv  = "PSNOKA,NIK,NAMA,SEGMEN,BULAN,TAHUN\n";
    $csv .= "001,1234567890123458,Andi Wijaya,PBI APBD,2,2026\n";
    $file = UploadedFile::fake()->createWithContent('pbi-apbd.csv', $csv);

    \Livewire\Livewire::test(\App\Filament\Resources\KisPbiApbdImports\Pages\CreateKisPbiApbdImport::class)
        ->fillForm([
            'periode_bulan' => 2,
            'periode_tahun' => 2026,
            'file_path'     => $file,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('verifikator tidak bisa upload csv', function () {
    $user = User::factory()->verifikator()->create();

    $this->actingAs($user)
         ->get('/admin/kis-pbi-apbd-imports/create')
         ->assertForbidden();
});

// ================================================================
// CHUNK JOB — PROSES DATA
// ================================================================

it('chunk job menyimpan data member dengan benar', function () {
    $admin  = User::factory()->adminDinsos()->create();
    $import = KisPbiApbdImport::create([
        'original_filename' => 'test.csv',
        'file_path'         => 'kis-imports/test.csv',
        'periode_bulan'     => 3,
        'periode_tahun'     => 2026,
        'status'            => 'processing',
        'uploaded_by'       => $admin->id,
    ]);

    $rows = [
        ['001', '1234567890123456', 'Budi Santoso', 'PBI APBD', '3', '2026'],
        ['002', '1234567890123457', 'Siti Rahayu', 'PBI APBD', '3', '2026'],
        ['003', '1234567890123458', 'Andi Wijaya', 'PBI APBD', '3', '2026'],
    ];

    $job = new KisPbiApbdChunkJob($import->id, $rows);
    $job->handle();

    expect(KisPbiApbdMember::where('import_id', $import->id)->count())->toBe(3);

    $import->refresh();
    expect($import->processed_rows)->toBe(3)
        ->and($import->failed_rows)->toBe(0);
});

it('chunk job skip baris dengan nik kosong', function () {
    $admin  = User::factory()->adminDinsos()->create();
    $import = KisPbiApbdImport::create([
        'original_filename' => 'test.csv',
        'file_path'         => 'kis-imports/test.csv',
        'periode_bulan'     => 4,
        'periode_tahun'     => 2026,
        'status'            => 'processing',
        'uploaded_by'       => $admin->id,
    ]);

    $rows = [
        ['001', '', 'Tanpa NIK', 'PBI APBD', '4', '2026'],        // NIK kosong
        ['002', '1234567890123456', 'Valid', 'PBI APBD', '4', '2026'], // valid
    ];

    $job = new KisPbiApbdChunkJob($import->id, $rows);
    $job->handle();

    $import->refresh();
    expect($import->processed_rows)->toBe(1)
        ->and($import->failed_rows)->toBe(1);
});

it('chunk job upsert data yang sudah ada', function () {
    $admin  = User::factory()->adminDinsos()->create();
    $import = KisPbiApbdImport::create([
        'original_filename' => 'test.csv',
        'file_path'         => 'kis-imports/test.csv',
        'periode_bulan'     => 5,
        'periode_tahun'     => 2026,
        'status'            => 'processing',
        'uploaded_by'       => $admin->id,
    ]);

    $rows = [
        ['001', '1234567890123456', 'Nama Lama', 'PBI APBD', '5', '2026'],
    ];

    $job = new KisPbiApbdChunkJob($import->id, $rows);
    $job->handle();

    // Upload ulang dengan nama baru
    $rows2 = [
        ['001', '1234567890123456', 'Nama Baru', 'PBI APBD', '5', '2026'],
    ];

    $job2 = new KisPbiApbdChunkJob($import->id, $rows2);
    $job2->handle();

    // Tidak duplikat, nama terupdate
    expect(KisPbiApbdMember::where('nik', '1234567890123456')
        ->where('periode_bulan', 5)
        ->where('periode_tahun', 2026)
        ->count())->toBe(1);

    expect(KisPbiApbdMember::where('nik', '1234567890123456')
        ->first()->nama)->toBe('Nama Baru');
});

// ================================================================
// CEK NIK PAGE
// ================================================================

it('semua role bisa akses halaman cek kepesertaan kis', function () {
    foreach ([
        User::factory()->adminDinsos()->create(),
        User::factory()->operatorBidang()->create(),
        User::factory()->verifikator()->create(),
        User::factory()->operatorDesa()->create(),
    ] as $user) {
        $this->actingAs($user)
             ->get('/admin/cek-kepesertaan-kis')
             ->assertOk();
    }
});

it('cek nik menemukan data yang benar', function () {
    $admin  = User::factory()->adminDinsos()->create();
    $import = KisPbiApbdImport::create([
        'original_filename' => 'test.csv',
        'file_path'         => 'kis-imports/test.csv',
        'periode_bulan'     => 6,
        'periode_tahun'     => 2026,
        'status'            => 'done',
        'uploaded_by'       => $admin->id,
    ]);

    KisPbiApbdMember::create([
        'import_id'     => $import->id,
        'psnoka'        => '001',
        'nik'           => '5171234567890001',
        'nama'          => 'Made Sudana',
        'segmen'        => 'PBI APBD',
        'periode_bulan' => 6,
        'periode_tahun' => 2026,
    ]);

    $this->actingAs($admin);

    \Livewire\Livewire::test(\App\Filament\Pages\CekKepesertaanKis::class)
        ->set('data.nik', '5171234567890001')
        ->call('cari')
        ->assertSet('namaFound', 'Made Sudana')
        ->assertSet('hasSearched', true);
});

it('cek nik menampilkan kosong jika tidak ditemukan', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    \Livewire\Livewire::test(\App\Filament\Pages\CekKepesertaanKis::class)
        ->set('data.nik', '9999999999999999')
        ->call('cari')
        ->assertSet('namaFound', null)
        ->assertSet('hasSearched', true);
});

// ================================================================
// POLICY DOWNLOAD
// ================================================================

it('hanya admin dinsos yang bisa download csv', function () {
    $admin = User::factory()->adminDinsos()->create();
    $op    = User::factory()->operatorBidang()->create();

    $import = KisPbiApbdImport::create([
        'original_filename' => 'test.csv',
        'file_path'         => 'kis-imports/test.csv',
        'periode_bulan'     => 7,
        'periode_tahun'     => 2026,
        'status'            => 'done',
        'uploaded_by'       => $admin->id,
    ]);

    expect($admin->can('download', $import))->toBeTrue();
    expect($op->can('download', $import))->toBeFalse();
});
