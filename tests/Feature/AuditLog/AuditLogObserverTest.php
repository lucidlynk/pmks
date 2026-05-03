<?php

use App\Models\AuditLog;
use App\Models\Kecamatan;
use App\Models\Resident;
use App\Models\User;
use App\Models\Village;

it('otomatis mencatat log saat user dibuat', function () {
    User::factory()->create(['name' => 'User Baru']);

    expect(AuditLog::where('action', 'create')
        ->where('model_type', User::class)
        ->exists()
    )->toBeTrue();
});

it('otomatis mencatat log saat user diupdate', function () {
    $user = User::factory()->create(['name' => 'Nama Lama']);
    $user->update(['name' => 'Nama Baru']);

    $log = AuditLog::where('action', 'update')
        ->where('model_type', User::class)
        ->where('model_id', $user->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->old_values['name'])->toBe('Nama Lama')
        ->and($log->new_values['name'])->toBe('Nama Baru');
});

it('otomatis mencatat log saat user dihapus', function () {
    $user = User::factory()->create();
    $userId = $user->id;
    $user->delete();

    expect(AuditLog::where('action', 'delete')
        ->where('model_type', User::class)
        ->where('model_id', $userId)
        ->exists()
    )->toBeTrue();
});

it('otomatis mencatat log saat resident dibuat', function () {
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

    expect(AuditLog::where('action', 'create')
        ->where('model_type', Resident::class)
        ->exists()
    )->toBeTrue();
});

it('log hanya mencatat field yang berubah saat update', function () {
    $user = User::factory()->create([
        'name'  => 'Nama Awal',
        'email' => 'awal@test.com',
    ]);

    $user->update(['name' => 'Nama Berubah']);

    $log = AuditLog::where('action', 'update')
        ->where('model_type', User::class)
        ->where('model_id', $user->id)
        ->latest('id')
        ->first();

    expect($log->old_values)->toHaveKey('name')
        ->and($log->old_values)->not->toHaveKey('email');
});
