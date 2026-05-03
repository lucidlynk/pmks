<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;

it('admin dapat membuka halaman audit log', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user)->get('/admin/audit-logs')->assertOk();
});

it('audit log service dapat mencatat aksi', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    AuditLogService::log('test_action');

    expect(AuditLog::where('action', 'test_action')->exists())->toBeTrue();
});

it('audit log tidak bisa dihapus', function () {
    $user = User::factory()->adminDinsos()->create();

    $log = AuditLog::create([
        'user_id'    => $user->id,
        'action'     => 'test',
        'ip_address' => '127.0.0.1',
    ]);

    $log->delete();

    expect(AuditLog::find($log->id))->not->toBeNull();
});

it('audit log tidak bisa diupdate', function () {
    $user = User::factory()->adminDinsos()->create();

    $log = AuditLog::create([
        'user_id' => $user->id,
        'action'  => 'original_action',
    ]);

    $log->action = 'modified_action';
    $log->save();

    expect(AuditLog::find($log->id)->action)->toBe('original_action');
});

it('audit log mencatat user yang sedang login', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    AuditLogService::log('test_with_user');

    $log = AuditLog::where('action', 'test_with_user')->first();
    expect($log->user_id)->toBe($user->id);
});
