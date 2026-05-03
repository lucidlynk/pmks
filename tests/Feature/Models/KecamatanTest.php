<?php

use App\Models\Kecamatan;
use App\Models\Village;

it('can create kecamatan', function () {
    $kecamatan = Kecamatan::create([
        'name' => 'Buleleng',
        'code' => '001',
    ])->fresh();

    expect($kecamatan->name)->toBe('Buleleng')
        ->and($kecamatan->is_active)->toBeTrue();
});

it('has many villages', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);

    Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name'         => 'Desa Test',
        'code'         => 'V001',
        'type'         => 'desa',
    ]);

    expect($kecamatan->villages)->toHaveCount(1);
});

it('scope active only returns active kecamatan', function () {
    Kecamatan::create(['name' => 'Aktif',    'code' => 'K01', 'is_active' => true]);
    Kecamatan::create(['name' => 'Nonaktif', 'code' => 'K02', 'is_active' => false]);

    expect(Kecamatan::active()->count())->toBe(1);
});
