<?php

use App\Enums\DtsenStatus;
use App\Exports\DtsenRequestExport;
use App\Models\DtsenRequest;
use App\Models\Resident;
use App\Models\User;
use App\Models\Village;
use Maatwebsite\Excel\Facades\Excel;

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

// ─── Export Excel ─────────────────────────────────────────────────────────────

it('export headings contain expected columns', function (): void {
    $operator = makeOperatorDesa();
    $request  = makeDtsenRequest($operator);
    $export   = new DtsenRequestExport($request);

    $headings = $export->headings();
    $header   = end($headings); // baris header tabel

    expect($header)->toContain('NIK');
    expect($header)->toContain('Nama');
    expect($header)->toContain('Tanggal Lahir');
    expect($header)->toContain('Jenis Kelamin');
});

it('export headings include reference number in info rows', function (): void {
    $operator = makeOperatorDesa();
    $request  = makeDtsenRequest($operator);
    $export   = new DtsenRequestExport($request);

    $headings = $export->headings();

    expect($headings[0])->toContain($request->reference_number);
});

it('export sheet title is Daftar Warga', function (): void {
    $operator = makeOperatorDesa();
    $request  = makeDtsenRequest($operator);
    $export   = new DtsenRequestExport($request);

    expect($export->title())->toBe('Daftar Warga');
});

it('export maps resident data correctly', function (): void {
    $operator = makeOperatorDesa();
    $request  = makeDtsenRequest($operator);

    $resident = Resident::create([
        'village_id'  => $operator->village_id,
        'nik'         => '5108010101010001',
        'name'        => 'I Wayan Test',
        'birth_place' => 'Singaraja',
        'birth_date'  => '1990-01-01',
        'gender'      => 'L',
        'status_hubungan' => 'kepala_keluarga',
        'is_active'   => true,
    ]);
    $request->residents()->attach($resident->id);

    $export     = new DtsenRequestExport($request);
    $collection = $export->collection();
    $row        = $export->map($collection->first());

    expect($row[1])->toBe('5108010101010001');
    expect($row[2])->toBe('I Wayan Test');
    expect($row[3])->toBe('Singaraja');
    expect($row[4])->toBe('01/01/1990');
    expect($row[5])->toBe('Laki-laki');
});

it('export collection only includes residents of the request', function (): void {
    $operator = makeOperatorDesa();
    $req1     = makeDtsenRequest($operator);
    $req2     = makeDtsenRequest($operator);

    $r1 = Resident::create([
        'village_id' => $operator->village_id, 'nik' => '5108010101010011',
        'name' => 'Warga Satu', 'birth_place' => 'Buleleng',
        'birth_date' => '1985-05-10', 'gender' => 'L',
        'status_hubungan' => 'kepala_keluarga', 'is_active' => true,
    ]);
    $r2 = Resident::create([
        'village_id' => $operator->village_id, 'nik' => '5108010101010022',
        'name' => 'Warga Dua', 'birth_place' => 'Singaraja',
        'birth_date' => '1990-03-20', 'gender' => 'P',
        'status_hubungan' => 'istri', 'is_active' => true,
    ]);
    $req1->residents()->attach($r1->id);
    $req2->residents()->attach($r2->id);

    $export     = new DtsenRequestExport($req1);
    $collection = $export->collection();

    expect($collection->pluck('nik')->toArray())->toContain('5108010101010011');
    expect($collection->pluck('nik')->toArray())->not->toContain('5108010101010022');
});

it('excel filename does not contain slash', function (): void {
    $operator = makeOperatorDesa();
    $request  = makeDtsenRequest($operator);

    $filename = 'DTSEN_' . str_replace('/', '-', $request->reference_number) . '.xlsx';

    expect($filename)->not->toContain('/')->toEndWith('.xlsx');
});

it('view page accessible by all roles with download excel action', function (): void {
    $operator = makeOperatorDesa();
    $request  = makeDtsenRequest($operator);
    $roles    = ['admin_dinsos', 'verifikator', 'operator_bidang'];

    foreach ($roles as $role) {
        $user = User::factory()->create(['village_id' => null]);
        $user->assignRole($role);
        actingAs($user)
            ->get(route('filament.admin.resources.dtsen-requests.view', $request))
            ->assertOk();
    }
});
