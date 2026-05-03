<?php

use App\Models\Kecamatan;
use App\Models\Resident;
use App\Models\User;
use App\Models\Village;

it('admin dapat membuka halaman list penduduk', function () {
    $user = User::factory()->adminDinsos()->create();

    $this->actingAs($user)
         ->get('/admin/residents')
         ->assertOk();
});

it('dapat membuat data penduduk baru', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa',
    ]);

    \Livewire\Livewire::test(\App\Filament\Resources\Residents\Pages\CreateResident::class)
        ->fillForm([
            'kecamatan_id' => $kecamatan->id,
            'village_id'   => $village->id,
            'nik'          => '5108011234567890',
            'name'         => 'Budi Santoso',
            'birth_place'  => 'Singaraja',
            'birth_date'   => '1990-01-15',
            'gender'       => 'L',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Resident::where('nik', '5108011234567890')->exists())->toBeTrue();
});

it('nik harus unik', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa',
    ]);

    Resident::create([
        'village_id'  => $village->id,
        'nik'         => '5108011234567890',
        'name'        => 'Budi',
        'birth_place' => 'Singaraja',
        'birth_date'  => '1990-01-15',
        'gender'      => 'L',
    ]);

    expect(fn () => Resident::create([
        'village_id'  => $village->id,
        'nik'         => '5108011234567890',
        'name'        => 'Duplikat',
        'birth_place' => 'Singaraja',
        'birth_date'  => '1990-01-15',
        'gender'      => 'L',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('penduduk memiliki relasi ke desa dan kecamatan', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa',
    ]);

    $resident = Resident::create([
        'village_id'  => $village->id,
        'nik'         => '5108011234567890',
        'name'        => 'Budi',
        'birth_place' => 'Singaraja',
        'birth_date'  => '1990-01-15',
        'gender'      => 'L',
    ]);

    expect($resident->village->name)->toBe('Desa Test')
        ->and($resident->village->kecamatan->name)->toBe('Buleleng');
});
