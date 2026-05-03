<?php

use App\Models\Kecamatan;
use App\Models\Village;

it('belongs to kecamatan', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);

    $village = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name'         => 'Desa Bali',
        'code'         => 'V001',
        'type'         => 'desa',
    ]);

    expect($village->kecamatan->name)->toBe('Buleleng');
});

it('has correct type enum', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);

    $desa = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test',
        'code' => 'V001',
        'type' => 'desa',
    ]);

    $kelurahan = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Kelurahan Test',
        'code' => 'V002',
        'type' => 'kelurahan',
    ]);

    expect($desa->type)->toBe('desa')
        ->and($kelurahan->type)->toBe('kelurahan');
});
