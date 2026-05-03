<?php

use App\Models\Kecamatan;
use App\Models\User;

it('can render kecamatan list page', function () {
    $user = User::factory()->adminDinsos()->create();

    $this->actingAs($user)
         ->get('/admin/kecamatans')
         ->assertOk();
});

it('dapat membuat kecamatan baru', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    \Livewire\Livewire::test(\App\Filament\Resources\Kecamatans\Pages\CreateKecamatan::class)
        ->fillForm([
            'name'      => 'Buleleng',
            'code'      => '001',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Kecamatan::where('name', 'Buleleng')->exists())->toBeTrue();
});

it('dapat edit kecamatan', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    $kecamatan = Kecamatan::create([
        'name' => 'Lama',
        'code' => 'K99',
    ]);

    \Livewire\Livewire::test(\App\Filament\Resources\Kecamatans\Pages\EditKecamatan::class, [
        'record' => $kecamatan->getRouteKey(),
    ])
        ->fillForm(['name' => 'Baru'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($kecamatan->fresh()->name)->toBe('Baru');
});

it('kode kecamatan harus unik', function () {
    Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);

    expect(fn () => Kecamatan::create(['name' => 'Duplikat', 'code' => '001']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

it('kecamatan nonaktif tidak muncul di scope active', function () {
    Kecamatan::create(['name' => 'Aktif',    'code' => 'K01', 'is_active' => true]);
    Kecamatan::create(['name' => 'Nonaktif', 'code' => 'K02', 'is_active' => false]);

    expect(Kecamatan::active()->count())->toBe(1);
});
