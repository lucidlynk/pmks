<?php

use App\Models\FamilyCard;
use App\Models\Kecamatan;
use App\Models\User;
use App\Models\Village;

it('admin dapat membuka halaman list kartu keluarga', function () {
    $user = User::factory()->adminDinsos()->create();

    $this->actingAs($user)
         ->get('/admin/family-cards')
         ->assertOk();
});

it('dapat membuat kartu keluarga baru', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa',
    ]);

    \Livewire\Livewire::test(\App\Filament\Resources\FamilyCards\Pages\CreateFamilyCard::class)
        ->fillForm([
            'kecamatan_id'    => $kecamatan->id,
            'village_id'      => $village->id,
            'no_kk' 	      => '5108011234567890',
            'kepala_keluarga' => 'Budi Santoso',
            'address'         => 'Jl. Test No. 1',
            'rt'              => '001',
            'rw'              => '002',
            'is_active'       => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(FamilyCard::where('no_kk', '5108011234567890')->exists())->toBeTrue();
});

it('nomor kk harus 16 digit dan unik', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa',
    ]);

    FamilyCard::create([
        'village_id'      => $village->id,
        'no_kk'           => '5108011234567890',
        'kepala_keluarga' => 'Budi',
        'address'         => 'Jl. Test',
    ]);

    expect(fn () => FamilyCard::create([
        'village_id'      => $village->id,
        'no_kk'           => '5108011234567890',
        'kepala_keluarga' => 'Duplikat',
        'address'         => 'Jl. Test',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('kartu keluarga memiliki relasi ke desa dan kecamatan', function () {
    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa',
    ]);

    $familyCard = FamilyCard::create([
        'village_id'      => $village->id,
        'no_kk'           => '5108011234567890',
        'kepala_keluarga' => 'Budi',
        'address'         => 'Jl. Test',
    ]);

    expect($familyCard->village->name)->toBe('Desa Test')
        ->and($familyCard->village->kecamatan->name)->toBe('Buleleng');
});
