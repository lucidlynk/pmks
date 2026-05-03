<?php

use App\Models\User;

it('admin dinsos bisa akses semua halaman', function () {
    $user = User::factory()->adminDinsos()->create();
    $this->actingAs($user);

    $this->get('/admin/kecamatans')->assertOk();
    $this->get('/admin/villages')->assertOk();
    $this->get('/admin/users')->assertOk();
    $this->get('/admin/audit-logs')->assertOk();
    $this->get('/admin/pmks-categories')->assertOk();
    $this->get('/admin/psks-categories')->assertOk();
    $this->get('/admin/family-cards')->assertOk();
    $this->get('/admin/residents')->assertOk();
    $this->get('/admin/institutions')->assertOk();
    $this->get('/admin/submission-batches')->assertOk();
    $this->get('/admin/pmks-submissions')->assertOk();
    $this->get('/admin/psks-submissions')->assertOk();
});

it('operator desa tidak bisa akses halaman admin sistem', function () {
    $user = User::factory()->operatorDesa()->create();
    $this->actingAs($user);

    $this->get('/admin/kecamatans')->assertForbidden();
    $this->get('/admin/villages')->assertForbidden();
    $this->get('/admin/users')->assertForbidden();
    $this->get('/admin/audit-logs')->assertForbidden();
    $this->get('/admin/pmks-categories')->assertForbidden();
    $this->get('/admin/psks-categories')->assertForbidden();
});

it('operator desa bisa akses halaman data dan pengajuan', function () {
    $user = User::factory()->operatorDesa()->create();
    $this->actingAs($user);

    $this->get('/admin/family-cards')->assertOk();
    $this->get('/admin/residents')->assertOk();
    $this->get('/admin/institutions')->assertOk();
    $this->get('/admin/submission-batches')->assertOk();
    $this->get('/admin/pmks-submissions')->assertOk();
    $this->get('/admin/psks-submissions')->assertOk();
});

it('operator bidang tidak bisa akses halaman admin sistem', function () {
    $user = User::factory()->operatorBidang()->create();
    $this->actingAs($user);

    $this->get('/admin/users')->assertForbidden();
    $this->get('/admin/audit-logs')->assertForbidden();
    $this->get('/admin/kecamatans')->assertForbidden();
});

it('operator bidang bisa akses halaman data dan pengajuan', function () {
    $user = User::factory()->operatorBidang()->create();
    $this->actingAs($user);

    $this->get('/admin/family-cards')->assertOk();
    $this->get('/admin/residents')->assertOk();
    $this->get('/admin/institutions')->assertOk();
    $this->get('/admin/submission-batches')->assertOk();
    $this->get('/admin/pmks-submissions')->assertOk();
    $this->get('/admin/psks-submissions')->assertOk();
});

it('verifikator hanya bisa akses halaman pengajuan', function () {
    $user = User::factory()->verifikator()->create();
    $this->actingAs($user);

    $this->get('/admin/submission-batches')->assertOk();
    $this->get('/admin/pmks-submissions')->assertOk();
    $this->get('/admin/psks-submissions')->assertOk();

    $this->get('/admin/family-cards')->assertForbidden();
    $this->get('/admin/residents')->assertForbidden();
    $this->get('/admin/users')->assertForbidden();
    $this->get('/admin/audit-logs')->assertForbidden();
});
