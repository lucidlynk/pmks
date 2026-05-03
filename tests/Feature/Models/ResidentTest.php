<?php

use App\Models\FamilyCard;
use App\Models\Kecamatan;
use App\Models\Resident;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create resident with required fields', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa',
    ]);

    $resident = Resident::create([
        'village_id'  => $village->id,
        'nik'         => '5108011234567890',
        'name'        => 'Budi Santoso',
        'birth_place' => 'Singaraja',
        'birth_date'  => '1990-01-15',
        'gender'      => 'L',
    ]);

    expect($resident->nik)->toBe('5108011234567890')
        ->and($resident->gender)->toBe('L')
        ->and($resident->birth_date->format('Y-m-d'))->toBe('1990-01-15');
});

it('nik must be unique', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa',
    ]);

    $data = [
        'village_id'  => $village->id,
        'nik'         => '5108011234567890',
        'name'        => 'Budi',
        'birth_place' => 'Singaraja',
        'birth_date'  => '1990-01-15',
        'gender'      => 'L',
    ];

    Resident::create($data);

    expect(fn () => Resident::create($data))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
