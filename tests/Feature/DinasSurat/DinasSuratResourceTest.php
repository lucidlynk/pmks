<?php

use App\Models\DinasSurat;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('semua role bisa akses halaman list surat dinas', function () {
    foreach ([
        User::factory()->adminDinsos()->create(),
        User::factory()->operatorBidang()->create(),
        User::factory()->verifikator()->create(),
        User::factory()->operatorDesa()->create(),
    ] as $user) {
        $this->actingAs($user)
             ->get('/admin/dinas-surats')
             ->assertOk();
    }
});

it('hanya admin dinsos yang bisa akses halaman create', function () {
    $admin = User::factory()->adminDinsos()->create();
    $this->actingAs($admin)
         ->get('/admin/dinas-surats/create')
         ->assertOk();
});

it('operator desa tidak bisa akses halaman create', function () {
    $user = User::factory()->operatorDesa()->create();
    $this->actingAs($user)
         ->get('/admin/dinas-surats/create')
         ->assertForbidden();
});

it('operator bidang tidak bisa akses halaman create', function () {
    $user = User::factory()->operatorBidang()->create();
    $this->actingAs($user)
         ->get('/admin/dinas-surats/create')
         ->assertForbidden();
});

it('admin dinsos bisa upload surat dinas', function () {
    Storage::fake('local');

    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    $file = UploadedFile::fake()->create('surat-edaran.pdf', 500, 'application/pdf');

    \Livewire\Livewire::test(\App\Filament\Resources\DinasSurats\Pages\CreateDinasSurat::class)
        ->fillForm([
            'judul'         => 'Surat Edaran Bansos 2026',
            'nomor_surat'   => '400/123/Dinsos/2026',
            'tanggal_surat' => '2026-05-30',
            'kategori'      => 'edaran',
            'target_scope'  => 'semua',
            'is_active'     => true,
            'file_path'     => $file,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $surat = DinasSurat::first();
    expect($surat)->not->toBeNull()
        ->and($surat->judul)->toBe('Surat Edaran Bansos 2026')
        ->and($surat->kategori)->toBe('edaran')
        ->and($surat->uploaded_by)->toBe($user->id);
});

it('judul surat wajib diisi', function () {
    Storage::fake('local');

    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    \Livewire\Livewire::test(\App\Filament\Resources\DinasSurats\Pages\CreateDinasSurat::class)
        ->fillForm([
            'judul'         => '',
            'tanggal_surat' => '2026-05-30',
            'kategori'      => 'edaran',
            'target_scope'  => 'semua',
            'file_path'     => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
        ])
        ->call('create')
        ->assertHasFormErrors(['judul']);
});

it('kategori label benar', function () {
    $admin = User::factory()->adminDinsos()->create();
    $surat = DinasSurat::create([
        'judul'             => 'Test',
        'tanggal_surat'     => now(),
        'kategori'          => 'sk',
        'target_scope'      => 'semua',
        'file_path'         => 'dinas-surats/test.pdf',
        'original_filename' => 'test.pdf',
        'file_size'         => 1024,
        'uploaded_by'       => $admin->id,
    ]);

    expect($surat->kategori_label)->toBe('Surat Keputusan')
        ->and($surat->kategori_color)->toBe('warning');
});

it('file size label benar', function () {
    $admin = User::factory()->adminDinsos()->create();
    $surat = DinasSurat::create([
        'judul'             => 'Test',
        'tanggal_surat'     => now(),
        'kategori'          => 'edaran',
        'target_scope'      => 'semua',
        'file_path'         => 'dinas-surats/test.pdf',
        'original_filename' => 'test.pdf',
        'file_size'         => 1048576,
        'uploaded_by'       => $admin->id,
    ]);

    expect($surat->file_size_label)->toBe('1 MB');
});

it('soft delete berfungsi', function () {
    $admin = User::factory()->adminDinsos()->create();
    $surat = DinasSurat::create([
        'judul'             => 'Test Delete',
        'tanggal_surat'     => now(),
        'kategori'          => 'edaran',
        'target_scope'      => 'semua',
        'file_path'         => 'dinas-surats/test.pdf',
        'original_filename' => 'test.pdf',
        'file_size'         => 1024,
        'uploaded_by'       => $admin->id,
    ]);

    $surat->delete();

    expect(DinasSurat::find($surat->id))->toBeNull()
        ->and(DinasSurat::withTrashed()->find($surat->id))->not->toBeNull();
});

it('hanya admin dinsos yang bisa create surat', function () {
    $admin = User::factory()->adminDinsos()->create();
    $op    = User::factory()->operatorBidang()->create();
    $desa  = User::factory()->operatorDesa()->create();

    expect($admin->can('create', DinasSurat::class))->toBeTrue()
        ->and($op->can('create', DinasSurat::class))->toBeFalse()
        ->and($desa->can('create', DinasSurat::class))->toBeFalse();
});

it('semua role bisa view surat', function () {
    $admin = User::factory()->adminDinsos()->create();
    $surat = DinasSurat::create([
        'judul'             => 'Test View',
        'tanggal_surat'     => now(),
        'kategori'          => 'edaran',
        'target_scope'      => 'semua',
        'file_path'         => 'dinas-surats/test.pdf',
        'original_filename' => 'test.pdf',
        'file_size'         => 1024,
        'uploaded_by'       => $admin->id,
    ]);

    foreach ([
        User::factory()->adminDinsos()->create(),
        User::factory()->operatorBidang()->create(),
        User::factory()->verifikator()->create(),
        User::factory()->operatorDesa()->create(),
    ] as $user) {
        expect($user->can('view', $surat))->toBeTrue();
    }
});

it('hanya admin dinsos yang bisa delete surat', function () {
    $admin = User::factory()->adminDinsos()->create();
    $op    = User::factory()->operatorBidang()->create();

    $surat = DinasSurat::create([
        'judul'             => 'Test Delete Policy',
        'tanggal_surat'     => now(),
        'kategori'          => 'edaran',
        'target_scope'      => 'semua',
        'file_path'         => 'dinas-surats/test.pdf',
        'original_filename' => 'test.pdf',
        'file_size'         => 1024,
        'uploaded_by'       => $admin->id,
    ]);

    expect($admin->can('delete', $surat))->toBeTrue()
        ->and($op->can('delete', $surat))->toBeFalse();
});
