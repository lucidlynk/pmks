<?php

use App\Models\Institution;
use App\Models\Kecamatan;
use App\Models\User;
use App\Models\Village;

it('admin dapat membuka halaman list lembaga', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user)->get('/admin/institutions')->assertOk();
});

it('dapat membuat lembaga baru', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa',
    ]);

    \Livewire\Livewire::test(\App\Filament\Resources\Institutions\Pages\CreateInstitution::class)
        ->fillForm([
            'kecamatan_id' => $kecamatan->id,
            'village_id'   => $village->id,
            'name'         => 'Karang Taruna Muda Jaya',
            'type'         => 'karang_taruna',
            'is_active'    => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Institution::where('name', 'Karang Taruna Muda Jaya')->exists())->toBeTrue();
});

it('lembaga memiliki relasi ke desa dan kecamatan', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa',
    ]);

    $institution = Institution::create([
        'village_id' => $village->id,
        'name'       => 'PKK Desa Test',
        'type'       => 'pkk',
    ]);

    expect($institution->village->name)->toBe('Desa Test')
        ->and($institution->village->kecamatan->name)->toBe('Buleleng');
});

it('jenis lembaga harus valid', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa',
    ]);

    $institution = Institution::create([
        'village_id' => $village->id,
        'name'       => 'LKS Test',
        'type'       => 'lks',
    ]);

    expect($institution->type)->toBeIn([
        'karang_taruna', 'pkk', 'lks', 'lksa', 'lembaga_sosial', 'orsos', 'other',
    ]);
});
