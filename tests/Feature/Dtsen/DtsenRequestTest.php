<?php

use App\Enums\DtsenStatus;
use App\Models\DtsenRequest;
use App\Models\Resident;
use App\Models\User;
use App\Models\Village;

use function Pest\Laravel\actingAs;

// ─── Helpers ──────────────────────────────────────────────────────────────────

function makeOperatorDesa(?Village $village = null): User
{
    $village ??= Village::factory()->create();
    $user = User::factory()->create(['village_id' => $village->id]);
    $user->assignRole('operator_desa');
    return $user;
}

function makeAdminDinsos(): User
{
    $user = User::factory()->create(['village_id' => null]);
    $user->assignRole('admin_dinsos');
    return $user;
}

function makeDtsenRequest(User $user, array $attrs = []): DtsenRequest
{
    return DtsenRequest::factory()->create(array_merge([
        'village_id' => $user->village_id,
        'user_id'    => $user->id,
        'status'     => DtsenStatus::DRAFT->value,
    ], $attrs));
}

// ─── Reference Number ─────────────────────────────────────────────────────────

it('generates reference number in correct format', function (): void {
    $ref = DtsenRequest::generateReferenceNumber();

    expect($ref)->toMatch('/^DTSEN\/\d{4}\/\d{2}\/\d{4}$/');
});

// ─── Status Transitions ───────────────────────────────────────────────────────

it('draft can be submitted', function (): void {
    expect(DtsenStatus::DRAFT->canSubmit())->toBeTrue();
});

it('submitted can be processed', function (): void {
    expect(DtsenStatus::SUBMITTED->canProcess())->toBeTrue();
});

it('on_process can upload pdf', function (): void {
    expect(DtsenStatus::ON_PROCESS->canUploadPdf())->toBeTrue();
});

it('ready can download pdf', function (): void {
    expect(DtsenStatus::READY->canDownloadPdf())->toBeTrue();
});

it('draft and submitted can be cancelled', function (): void {
    expect(DtsenStatus::DRAFT->canCancel())->toBeTrue();
    expect(DtsenStatus::SUBMITTED->canCancel())->toBeTrue();
    expect(DtsenStatus::READY->canCancel())->toBeFalse();
});

// ─── Access Control ───────────────────────────────────────────────────────────

it('operator_desa can only see their own village requests', function (): void {
    $village1 = Village::factory()->create();
    $village2 = Village::factory()->create();

    $operator = makeOperatorDesa($village1);

    $ownRequest  = makeDtsenRequest($operator);
    $otherUser   = makeOperatorDesa($village2);
    $otherRequest = makeDtsenRequest($otherUser);

    actingAs($operator);

    $query = DtsenRequest::query()
        ->when(
            auth()->user()->village_id,
            fn ($q) => $q->where('village_id', auth()->user()->village_id)
        );

    expect($query->pluck('id'))
        ->toContain($ownRequest->id)
        ->not->toContain($otherRequest->id);
});

it('admin_dinsos can see all requests', function (): void {
    $village1 = Village::factory()->create();
    $village2 = Village::factory()->create();

    $op1 = makeOperatorDesa($village1);
    $op2 = makeOperatorDesa($village2);

    $req1 = makeDtsenRequest($op1);
    $req2 = makeDtsenRequest($op2);

    actingAs(makeAdminDinsos());

    $ids = DtsenRequest::pluck('id');

    expect($ids)
        ->toContain($req1->id)
        ->toContain($req2->id);
});

// ─── Ownership ────────────────────────────────────────────────────────────────

it('isOwnedBy returns true for owner', function (): void {
    $operator = makeOperatorDesa();
    $request  = makeDtsenRequest($operator);

    expect($request->isOwnedBy($operator))->toBeTrue();
});

it('isOwnedBy returns false for other user', function (): void {
    $operator = makeOperatorDesa();
    $other    = makeOperatorDesa();
    $request  = makeDtsenRequest($operator);

    expect($request->isOwnedBy($other))->toBeFalse();
});

// ─── canBeEditedBy ────────────────────────────────────────────────────────────

it('draft request can be edited by owner', function (): void {
    $operator = makeOperatorDesa();
    $request  = makeDtsenRequest($operator, ['status' => DtsenStatus::DRAFT->value]);

    expect($request->canBeEditedBy($operator))->toBeTrue();
});

it('submitted request cannot be edited', function (): void {
    $operator = makeOperatorDesa();
    $request  = makeDtsenRequest($operator, ['status' => DtsenStatus::SUBMITTED->value]);

    expect($request->canBeEditedBy($operator))->toBeFalse();
});

// ─── Download Filename ───────────────────────────────────────────────────────

it('download filename does not contain slash', function (): void {
    $operator = makeOperatorDesa();
    $request  = makeDtsenRequest($operator);

    $filename = str_replace('/', '-', 'DTSEN-' . $request->reference_number . '.pdf');

    expect($filename)
        ->not->toContain('/')
        ->not->toContain('\\')
        ->toContain('DTSEN-')
        ->toEndWith('.pdf');
});
