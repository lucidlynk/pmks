<?php

use App\Enums\DtsenStatus;
use App\Enums\UserRole;
use App\Models\DtsenRequest;
use App\Models\User;
use App\Models\Village;
use App\Policies\DtsenRequestPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

// Jalankan seeder agar data village, role, permission tersedia
beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    $this->seed(\Database\Seeders\StafDinsosRoleSeeder::class);
});

// -------------------------------------------------------------------------
// Helper
// -------------------------------------------------------------------------

function makeUser(string $role, ?int $villageId = null): User
{
    $user = User::factory()->create([
        'village_id' => $villageId,
        'is_active'  => true,
    ]);
    $user->assignRole($role);

    return $user;
}

function makeDtsenRequest(array $attrs = []): DtsenRequest
{
    $village = Village::first();

    return DtsenRequest::create(array_merge([
        'reference_number' => DtsenRequest::generateReferenceNumber(),
        'village_id'       => $village->id,
        'user_id'          => makeUser(UserRole::OPERATOR_DESA->value, $village->id)->id,
        'status'           => DtsenStatus::DRAFT,
        'purpose'          => 'Keperluan BPJS',
    ], $attrs));
}

// -------------------------------------------------------------------------
// DtsenStatus enum
// -------------------------------------------------------------------------

describe('DtsenStatus', function () {
    it('hanya DRAFT yang bisa di-submit', function () {
        expect(DtsenStatus::DRAFT->canSubmit())->toBeTrue();
        expect(DtsenStatus::SUBMITTED->canSubmit())->toBeFalse();
        expect(DtsenStatus::ON_PROCESS->canSubmit())->toBeFalse();
        expect(DtsenStatus::READY->canSubmit())->toBeFalse();
        expect(DtsenStatus::CANCELLED->canSubmit())->toBeFalse();
    });

    it('hanya DRAFT dan SUBMITTED yang bisa dibatalkan', function () {
        expect(DtsenStatus::DRAFT->canCancel())->toBeTrue();
        expect(DtsenStatus::SUBMITTED->canCancel())->toBeTrue();
        expect(DtsenStatus::ON_PROCESS->canCancel())->toBeFalse();
        expect(DtsenStatus::READY->canCancel())->toBeFalse();
        expect(DtsenStatus::CANCELLED->canCancel())->toBeFalse();
    });

    it('hanya DRAFT dan CANCELLED yang bisa dihapus', function () {
        expect(DtsenStatus::DRAFT->canDelete())->toBeTrue();
        expect(DtsenStatus::CANCELLED->canDelete())->toBeTrue();
        expect(DtsenStatus::SUBMITTED->canDelete())->toBeFalse();
        expect(DtsenStatus::ON_PROCESS->canDelete())->toBeFalse();
        expect(DtsenStatus::READY->canDelete())->toBeFalse();
    });

    it('hanya ON_PROCESS yang bisa upload PDF', function () {
        expect(DtsenStatus::ON_PROCESS->canUploadPdf())->toBeTrue();
        expect(DtsenStatus::DRAFT->canUploadPdf())->toBeFalse();
        expect(DtsenStatus::READY->canUploadPdf())->toBeFalse();
    });

    it('hanya READY yang bisa download PDF', function () {
        expect(DtsenStatus::READY->canDownloadPdf())->toBeTrue();
        expect(DtsenStatus::ON_PROCESS->canDownloadPdf())->toBeFalse();
        expect(DtsenStatus::DRAFT->canDownloadPdf())->toBeFalse();
    });
});

// -------------------------------------------------------------------------
// Reference number generator
// -------------------------------------------------------------------------

describe('generateReferenceNumber', function () {
    it('menghasilkan format yang benar', function () {
        $number = DtsenRequest::generateReferenceNumber();
        $year   = now()->format('Y');
        $month  = now()->format('m');

        expect($number)->toMatch("/^DTSEN\/{$year}\/{$month}\/\d{4}$/");
    });

    it('nomor urut bertambah setiap kali ada record baru', function () {
        $first = DtsenRequest::generateReferenceNumber();
        makeDtsenRequest(['reference_number' => $first]);

        $second = DtsenRequest::generateReferenceNumber();

        expect($second)->not->toBe($first);

        $firstSeq  = (int) substr($first, -4);
        $secondSeq = (int) substr($second, -4);
        expect($secondSeq)->toBe($firstSeq + 1);
    });
});

// -------------------------------------------------------------------------
// Status transitions
// -------------------------------------------------------------------------

describe('DtsenRequest status transitions', function () {
    it('bisa submit dari DRAFT', function () {
        $req = makeDtsenRequest(['status' => DtsenStatus::DRAFT]);
        $req->submit();

        expect($req->fresh()->status)->toBe(DtsenStatus::SUBMITTED);
    });

    it('tidak bisa submit dari selain DRAFT', function () {
        $req = makeDtsenRequest(['status' => DtsenStatus::SUBMITTED]);

        expect(fn () => $req->submit())
            ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });

    it('bisa cancel dari DRAFT', function () {
        $req = makeDtsenRequest(['status' => DtsenStatus::DRAFT]);
        $req->cancel();

        expect($req->fresh()->status)->toBe(DtsenStatus::CANCELLED);
    });

    it('bisa cancel dari SUBMITTED', function () {
        $req = makeDtsenRequest(['status' => DtsenStatus::SUBMITTED]);
        $req->cancel();

        expect($req->fresh()->status)->toBe(DtsenStatus::CANCELLED);
    });

    it('tidak bisa cancel dari ON_PROCESS', function () {
        $req = makeDtsenRequest(['status' => DtsenStatus::ON_PROCESS]);

        expect(fn () => $req->cancel())
            ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });

    it('staf bisa process dari SUBMITTED', function () {
        $req  = makeDtsenRequest(['status' => DtsenStatus::SUBMITTED]);
        $staf = makeUser(UserRole::STAF_DINSOS->value);
        $req->process($staf);

        expect($req->fresh()->status)->toBe(DtsenStatus::ON_PROCESS);
        expect($req->fresh()->processed_by)->toBe($staf->id);
    });

    it('bisa markReady dari ON_PROCESS', function () {
        $req = makeDtsenRequest(['status' => DtsenStatus::ON_PROCESS]);
        $req->markReady();

        expect($req->fresh()->status)->toBe(DtsenStatus::READY);
    });
});

// -------------------------------------------------------------------------
// Policy
// -------------------------------------------------------------------------

describe('DtsenRequestPolicy', function () {
    it('operator desa hanya bisa lihat permohonan desanya sendiri', function () {
        $village     = Village::first();
        $otherVillage = Village::skip(1)->first();
        $operator    = makeUser(UserRole::OPERATOR_DESA->value, $village->id);
        $req         = makeDtsenRequest(['village_id' => $village->id]);
        $reqOther    = makeDtsenRequest(['village_id' => $otherVillage->id]);

        $policy = new DtsenRequestPolicy();
        expect($policy->view($operator, $req))->toBeTrue();
        expect($policy->view($operator, $reqOther))->toBeFalse();
    });

    it('operator desa tidak bisa upload PDF', function () {
        $village  = Village::first();
        $operator = makeUser(UserRole::OPERATOR_DESA->value, $village->id);
        $req      = makeDtsenRequest([
            'village_id' => $village->id,
            'status'     => DtsenStatus::ON_PROCESS,
        ]);

        $policy = new DtsenRequestPolicy();
        expect($policy->uploadPdf($operator, $req))->toBeFalse();
    });

    it('staf dinsos bisa upload PDF saat ON_PROCESS', function () {
        $staf = makeUser(UserRole::STAF_DINSOS->value);
        $req  = makeDtsenRequest(['status' => DtsenStatus::ON_PROCESS]);

        $policy = new DtsenRequestPolicy();
        expect($policy->uploadPdf($staf, $req))->toBeTrue();
    });

    it('staf dinsos tidak bisa upload PDF saat bukan ON_PROCESS', function () {
        $staf = makeUser(UserRole::STAF_DINSOS->value);
        $req  = makeDtsenRequest(['status' => DtsenStatus::SUBMITTED]);

        $policy = new DtsenRequestPolicy();
        expect($policy->uploadPdf($staf, $req))->toBeFalse();
    });

    it('operator desa hanya bisa download PDF milik desanya saat READY', function () {
        $village      = Village::first();
        $otherVillage = Village::skip(1)->first();
        $operator     = makeUser(UserRole::OPERATOR_DESA->value, $village->id);
        $req          = makeDtsenRequest([
            'village_id' => $village->id,
            'status'     => DtsenStatus::READY,
        ]);
        $reqOther     = makeDtsenRequest([
            'village_id' => $otherVillage->id,
            'status'     => DtsenStatus::READY,
        ]);

        $policy = new DtsenRequestPolicy();
        expect($policy->downloadPdf($operator, $req))->toBeTrue();
        expect($policy->downloadPdf($operator, $reqOther))->toBeFalse();
    });

    it('operator desa tidak bisa download PDF saat belum READY', function () {
        $village  = Village::first();
        $operator = makeUser(UserRole::OPERATOR_DESA->value, $village->id);
        $req      = makeDtsenRequest([
            'village_id' => $village->id,
            'status'     => DtsenStatus::ON_PROCESS,
        ]);

        $policy = new DtsenRequestPolicy();
        expect($policy->downloadPdf($operator, $req))->toBeFalse();
    });

    it('hanya admin yang bisa deleteAny', function () {
        $admin    = makeUser(UserRole::ADMIN_DINSOS->value);
        $operator = makeUser(UserRole::OPERATOR_DESA->value);
        $staf     = makeUser(UserRole::STAF_DINSOS->value);

        $policy = new DtsenRequestPolicy();
        expect($policy->deleteAny($admin))->toBeTrue();
        expect($policy->deleteAny($operator))->toBeFalse();
        expect($policy->deleteAny($staf))->toBeFalse();
    });
});
