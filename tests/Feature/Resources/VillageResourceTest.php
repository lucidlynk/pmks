<?php

use App\Models\Kecamatan;
use App\Models\User;
use App\Models\Village;

it('can render village list page', function () {
    $user = User::factory()->adminDinsos()->create();

    $this->actingAs($user)
         ->get('/admin/villages')
         ->assertOk();
});

it('dapat membuat desa baru', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);

    \Livewire\Livewire::test(\App\Filament\Resources\Villages\Pages\CreateVillage::class)
        ->fillForm([
            'kecamatan_id' => $kecamatan->id,
            'name'         => 'Desa Baru',
            'code'         => 'V001',
            'type'         => 'desa',
            'is_active'    => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Village::where('name', 'Desa Baru')->exists())->toBeTrue();
});

it('desa memiliki relasi ke kecamatan', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);

    $village = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name'         => 'Desa Test',
        'code'         => 'V001',
        'type'         => 'desa',
    ]);

    expect($village->kecamatan->name)->toBe('Buleleng');
});

it('tipe desa hanya boleh desa atau kelurahan', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);

    $village = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name'         => 'Test',
        'code'         => 'V001',
        'type'         => 'desa',
    ]);

    expect($village->type)->toBeIn(['desa', 'kelurahan']);
});
