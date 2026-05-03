<?php

use App\Enums\UserRole;
use App\Models\Kecamatan;
use App\Models\User;
use App\Models\Village;

it('admin dapat membuka halaman list user', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user)->get('/admin/users')->assertOk();
});

it('admin dapat membuat user baru dengan role operator bidang', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    \Livewire\Livewire::test(\App\Filament\Resources\Users\Pages\CreateUser::class)
        ->fillForm([
            'name'      => 'Operator Baru',
            'email'     => 'operator@test.com',
            'password'  => 'password123',
            'roles'     => UserRole::OPERATOR_BIDANG->value,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(User::where('email', 'operator@test.com')->exists())->toBeTrue();
});

it('admin dapat membuat user operator desa dengan village', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    $kecamatan = Kecamatan::create(['name' => 'Buleleng', 'code' => '001']);
    $village   = Village::create([
        'kecamatan_id' => $kecamatan->id,
        'name' => 'Desa Test', 'code' => 'V001', 'type' => 'desa',
    ]);

    \Livewire\Livewire::test(\App\Filament\Resources\Users\Pages\CreateUser::class)
        ->fillForm([
            'name'         => 'Operator Desa Baru',
            'email'        => 'opdes@test.com',
            'password'     => 'password123',
            'roles'        => UserRole::OPERATOR_DESA->value,
            'kecamatan_id' => $kecamatan->id,
            'village_id'   => $village->id,
            'is_active'    => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $newUser = User::where('email', 'opdes@test.com')->first();
    expect($newUser)->not->toBeNull()
        ->and($newUser->village_id)->toBe($village->id);
});

it('email user harus unik', function () {
    User::factory()->create(['email' => 'sama@test.com']);

    expect(fn () => User::factory()->create(['email' => 'sama@test.com']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

it('user nonaktif tidak bisa akses panel', function () {
    $user = User::factory()->inactive()->adminDinsos()->create();
    expect($user->canAccessPanel(app(\Filament\Panel::class)))->toBeFalse();
});

it('user aktif bisa akses panel', function () {
    $user = User::factory()->adminDinsos()->create();
    expect($user->canAccessPanel(app(\Filament\Panel::class)))->toBeTrue();
});
