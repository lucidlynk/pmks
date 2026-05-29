<?php

use App\Models\KisRekap;
use App\Models\User;

// ================================================================
// AKSES & NAVIGASI
// ================================================================

it('admin dinsos bisa akses halaman rekap kis', function () {
    $user = User::factory()->adminDinsos()->create();

    $this->actingAs($user)
         ->get('/admin/kis-rekaps')
         ->assertOk();
});

it('operator bidang bisa akses halaman rekap kis', function () {
    $user = User::factory()->operatorBidang()->create();

    $this->actingAs($user)
         ->get('/admin/kis-rekaps')
         ->assertOk();
});

it('verifikator bisa akses halaman rekap kis', function () {
    $user = User::factory()->verifikator()->create();

    $this->actingAs($user)
         ->get('/admin/kis-rekaps')
         ->assertOk();
});

it('operator desa bisa akses halaman rekap kis', function () {
    $user = User::factory()->operatorDesa()->create();

    $this->actingAs($user)
         ->get('/admin/kis-rekaps')
         ->assertOk();
});

// ================================================================
// CREATE
// ================================================================

it('admin dinsos bisa membuat rekap kis', function () {
    $user = User::factory()->adminDinsos()->create();

    $this->actingAs($user);

    \Livewire\Livewire::test(\App\Filament\Resources\KisRekaps\Pages\CreateKisRekap::class)
        ->fillForm([
            'periode_bulan' => 1,
            'periode_tahun' => 2026,
            'pbi_apbd'      => 1000,
            'pbi_apbn'      => 2000,
            'ppu'           => 500,
            'pbpu'          => 300,
            'bp'            => 200,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $rekap = KisRekap::where('periode_bulan', 1)
                     ->where('periode_tahun', 2026)
                     ->first();

    expect($rekap)->not->toBeNull()
        ->and($rekap->pbi_apbd)->toBe(1000)
        ->and($rekap->total)->toBe(4000); // 1000+2000+500+300+200
});

it('operator bidang bisa membuat rekap kis', function () {
    $user = User::factory()->operatorBidang()->create();

    $this->actingAs($user);

    \Livewire\Livewire::test(\App\Filament\Resources\KisRekaps\Pages\CreateKisRekap::class)
        ->fillForm([
            'periode_bulan' => 2,
            'periode_tahun' => 2026,
            'pbi_apbd'      => 500,
            'pbi_apbn'      => 1000,
            'ppu'           => 200,
            'pbpu'          => 100,
            'bp'            => 50,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('operator desa tidak bisa membuat rekap kis', function () {
    $user = User::factory()->operatorDesa()->create();

    $this->actingAs($user)
         ->get('/admin/kis-rekaps/create')
         ->assertForbidden();
});

it('verifikator tidak bisa membuat rekap kis', function () {
    $user = User::factory()->verifikator()->create();

    $this->actingAs($user)
         ->get('/admin/kis-rekaps/create')
         ->assertForbidden();
});

// ================================================================
// TOTAL AUTO-COMPUTE
// ================================================================

it('total otomatis terhitung saat simpan', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    $rekap = KisRekap::create([
        'periode_bulan' => 3,
        'periode_tahun' => 2026,
        'pbi_apbd'      => 100,
        'pbi_apbn'      => 200,
        'ppu'           => 300,
        'pbpu'          => 400,
        'bp'            => 500,
        'created_by'    => $user->id,
        'updated_by'    => $user->id,
    ]);

    expect($rekap->total)->toBe(1500);
});

it('total terupdate saat data diubah', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    $rekap = KisRekap::create([
        'periode_bulan' => 4,
        'periode_tahun' => 2026,
        'pbi_apbd'      => 100,
        'pbi_apbn'      => 100,
        'ppu'           => 100,
        'pbpu'          => 100,
        'bp'            => 100,
        'created_by'    => $user->id,
        'updated_by'    => $user->id,
    ]);

    $rekap->update(['pbi_apbd' => 500]);

    expect($rekap->fresh()->total)->toBe(900); // 500+100+100+100+100
});

// ================================================================
// UNIQUE PERIODE
// ================================================================

it('tidak bisa membuat dua rekap untuk periode yang sama', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    KisRekap::create([
        'periode_bulan' => 5,
        'periode_tahun' => 2026,
        'pbi_apbd'      => 100,
        'pbi_apbn'      => 100,
        'ppu'           => 100,
        'pbpu'          => 100,
        'bp'            => 100,
        'created_by'    => $user->id,
        'updated_by'    => $user->id,
    ]);

    expect(fn () => KisRekap::create([
        'periode_bulan' => 5,
        'periode_tahun' => 2026,
        'pbi_apbd'      => 200,
        'pbi_apbn'      => 200,
        'ppu'           => 200,
        'pbpu'          => 200,
        'bp'            => 200,
        'created_by'    => $user->id,
        'updated_by'    => $user->id,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

// ================================================================
// DELETE
// ================================================================

it('admin dinsos bisa menghapus rekap kis', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    $rekap = KisRekap::create([
        'periode_bulan' => 6,
        'periode_tahun' => 2026,
        'pbi_apbd'      => 100,
        'pbi_apbn'      => 100,
        'ppu'           => 100,
        'pbpu'          => 100,
        'bp'            => 100,
        'created_by'    => $user->id,
        'updated_by'    => $user->id,
    ]);

    expect($user->can('delete', $rekap))->toBeTrue();
});

it('operator bidang tidak bisa menghapus rekap kis', function () {
    $user  = User::factory()->operatorBidang()->create();
    $admin = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    $rekap = KisRekap::create([
        'periode_bulan' => 7,
        'periode_tahun' => 2026,
        'pbi_apbd'      => 100,
        'pbi_apbn'      => 100,
        'ppu'           => 100,
        'pbpu'          => 100,
        'bp'            => 100,
        'created_by'    => $admin->id,
        'updated_by'    => $admin->id,
    ]);

    expect($user->can('delete', $rekap))->toBeFalse();
});
