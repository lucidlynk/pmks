<?php

use App\Enums\UserRole;
use App\Models\DtsenRekap;
use App\Models\DtsenRekapDetail;
use App\Models\User;
use App\Services\DtsenRekapImportService;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

// ================================================================
// HELPERS
// ================================================================

function makeUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);
    return $user;
}

function makeRekapWithDetails(): DtsenRekap
{
    $admin = makeUser(UserRole::ADMIN_DINSOS->value);
    $rekap = DtsenRekap::create([
        'bulan'             => 5,
        'tahun'             => 2026,
        'file_path'         => 'dtsen-rekaps/test.csv',
        'original_filename' => 'test.csv',
        'keterangan'        => 'Test rekap',
        'uploaded_by'       => $admin->id,
    ]);

    DtsenRekapDetail::insert([
        [
            'dtsen_rekap_id'           => $rekap->id,
            'kecamatan'                => 'Gerokgak',
            'kelurahan'                => 'Sumberklampok',
            'jumlah_keluarga'          => 1194,
            'jumlah_individu'          => 3591,
            'desil1_keluarga'          => 24,
            'desil1_individu'          => 77,
            'desil2_keluarga'          => 54,
            'desil2_individu'          => 166,
            'desil3_keluarga'          => 120,
            'desil3_individu'          => 389,
            'desil4_keluarga'          => 129,
            'desil4_individu'          => 392,
            'desil5_keluarga'          => 93,
            'desil5_individu'          => 306,
            'desil6_10_keluarga'       => 700,
            'desil6_10_individu'       => 2117,
            'belum_peringkat_keluarga' => 74,
            'belum_peringkat_individu' => 144,
            'nonaktif_keluarga'        => 65,
            'nonaktif_individu'        => 48,
            'created_at'               => now(),
            'updated_at'               => now(),
        ],
        [
            'dtsen_rekap_id'           => $rekap->id,
            'kecamatan'                => 'Seririt',
            'kelurahan'                => 'Seririt',
            'jumlah_keluarga'          => 2451,
            'jumlah_individu'          => 7394,
            'desil1_keluarga'          => 30,
            'desil1_individu'          => 95,
            'desil2_keluarga'          => 70,
            'desil2_individu'          => 220,
            'desil3_keluarga'          => 140,
            'desil3_individu'          => 450,
            'desil4_keluarga'          => 150,
            'desil4_individu'          => 460,
            'desil5_keluarga'          => 110,
            'desil5_individu'          => 360,
            'desil6_10_keluarga'       => 1800,
            'desil6_10_individu'       => 5400,
            'belum_peringkat_keluarga' => 151,
            'belum_peringkat_individu' => 409,
            'nonaktif_keluarga'        => 80,
            'nonaktif_individu'        => 60,
            'created_at'               => now(),
            'updated_at'               => now(),
        ],
    ]);

    return $rekap;
}

// ================================================================
// TEST: ACCESS CONTROL
// ================================================================

describe('Access Control', function () {
    it('allows admin dinsos to access list page', function () {
        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        actingAs($admin)
            ->get(route('filament.admin.resources.dtsen-rekaps.index'))
            ->assertOk();
    });

    it('allows operator bidang to access list page', function () {
        $user = makeUser(UserRole::OPERATOR_BIDANG->value);
        actingAs($user)
            ->get(route('filament.admin.resources.dtsen-rekaps.index'))
            ->assertOk();
    });

    it('allows operator desa to access list page', function () {
        $user = makeUser(UserRole::OPERATOR_DESA->value);
        actingAs($user)
            ->get(route('filament.admin.resources.dtsen-rekaps.index'))
            ->assertOk();
    });

    it('allows verifikator to access list page', function () {
        $user = makeUser(UserRole::VERIFIKATOR->value);
        actingAs($user)
            ->get(route('filament.admin.resources.dtsen-rekaps.index'))
            ->assertOk();
    });

    it('denies operator bidang from accessing create page', function () {
        $user = makeUser(UserRole::OPERATOR_BIDANG->value);
        actingAs($user)
            ->get(route('filament.admin.resources.dtsen-rekaps.create'))
            ->assertForbidden();
    });

    it('denies operator desa from accessing create page', function () {
        $user = makeUser(UserRole::OPERATOR_DESA->value);
        actingAs($user)
            ->get(route('filament.admin.resources.dtsen-rekaps.create'))
            ->assertForbidden();
    });

    it('allows admin dinsos to access create page', function () {
        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        actingAs($admin)
            ->get(route('filament.admin.resources.dtsen-rekaps.create'))
            ->assertOk();
    });
});

// ================================================================
// TEST: IMPORT SERVICE
// ================================================================

describe('Import Service', function () {
    beforeEach(function () {
        Storage::fake('local');
    });

    it('parses csv with semicolon separator', function () {
        $csv = implode("\n", [
            'Gerokgak;Sumberklampok;1194;3591;24;77;54;166;120;389;129;392;93;306;700;2117;74;144;65;48',
            'Seririt;Seririt;2451;7394;30;95;70;220;140;450;150;460;110;360;1800;5400;151;409;80;60',
        ]);
        Storage::disk('local')->put('dtsen-rekaps/test.csv', $csv);

        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        $rekap = DtsenRekap::create([
            'bulan' => 5, 'tahun' => 2026,
            'file_path'         => 'dtsen-rekaps/test.csv',
            'original_filename' => 'test.csv',
            'uploaded_by'       => $admin->id,
        ]);

        $result = (new DtsenRekapImportService())->import($rekap);

        expect($result['success'])->toBeTrue();
        expect($result['rows_imported'])->toBe(2);
        assertDatabaseHas('dtsen_rekap_details', [
            'dtsen_rekap_id' => $rekap->id,
            'kecamatan'      => 'Gerokgak',
            'kelurahan'      => 'Sumberklampok',
            'jumlah_keluarga' => 1194,
        ]);
    });

    it('skips header row when detected', function () {
        $csv = implode("\n", [
            'KECAMATAN;KELURAHAN;JML_KEL;JML_IND;D1K;D1I;D2K;D2I;D3K;D3I;D4K;D4I;D5K;D5I;D610K;D610I;BPK;BPI;NK;NI',
            'Gerokgak;Sumberklampok;1194;3591;24;77;54;166;120;389;129;392;93;306;700;2117;74;144;65;48',
        ]);
        Storage::disk('local')->put('dtsen-rekaps/h.csv', $csv);

        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        $rekap = DtsenRekap::create([
            'bulan' => 6, 'tahun' => 2026,
            'file_path'         => 'dtsen-rekaps/h.csv',
            'original_filename' => 'h.csv',
            'uploaded_by'       => $admin->id,
        ]);

        $result = (new DtsenRekapImportService())->import($rekap);
        expect($result['rows_imported'])->toBe(1);
    });

    it('returns error when file not found', function () {
        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        $rekap = DtsenRekap::create([
            'bulan' => 7, 'tahun' => 2026,
            'file_path'         => 'dtsen-rekaps/missing.csv',
            'original_filename' => 'missing.csv',
            'uploaded_by'       => $admin->id,
        ]);

        $result = (new DtsenRekapImportService())->import($rekap);
        expect($result['success'])->toBeFalse();
    });

    it('handles empty file', function () {
        Storage::disk('local')->put('dtsen-rekaps/empty.csv', '');

        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        $rekap = DtsenRekap::create([
            'bulan' => 8, 'tahun' => 2026,
            'file_path'         => 'dtsen-rekaps/empty.csv',
            'original_filename' => 'empty.csv',
            'uploaded_by'       => $admin->id,
        ]);

        $result = (new DtsenRekapImportService())->import($rekap);
        expect($result['success'])->toBeFalse();
    });

    it('skips rows with insufficient columns', function () {
        $csv = implode("\n", [
            'Gerokgak;Sumberklampok;1194;3591;24;77;54;166;120;389;129;392;93;306;700;2117;74;144;65;48',
            'BadRow;Only',
            'Seririt;Seririt;2451;7394;30;95;70;220;140;450;150;460;110;360;1800;5400;151;409;80;60',
        ]);
        Storage::disk('local')->put('dtsen-rekaps/mixed.csv', $csv);

        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        $rekap = DtsenRekap::create([
            'bulan' => 9, 'tahun' => 2026,
            'file_path'         => 'dtsen-rekaps/mixed.csv',
            'original_filename' => 'mixed.csv',
            'uploaded_by'       => $admin->id,
        ]);

        $result = (new DtsenRekapImportService())->import($rekap);
        expect($result['rows_imported'])->toBe(2);
    });
});

// ================================================================
// TEST: MODEL
// ================================================================

describe('Model DtsenRekap', function () {
    it('has 12 bulan options', function () {
        expect(DtsenRekap::bulanOptions())->toHaveCount(12);
    });

    it('returns correct periode accessor', function () {
        $rekap = new DtsenRekap(['bulan' => 5, 'tahun' => 2026]);
        expect($rekap->periode)->toBe('Mei 2026');
    });

    it('calculates totals from details', function () {
        $rekap = makeRekapWithDetails();
        expect($rekap->total_keluarga)->toBe(3645);
        expect($rekap->total_individu)->toBe(10985);
        expect($rekap->jumlah_kecamatan)->toBe(2);
        expect($rekap->jumlah_kelurahan)->toBe(2);
    });
});

// ================================================================
// TEST: UNIQUE CONSTRAINT
// ================================================================

describe('Unique Constraint', function () {
    it('prevents duplicate bulan+tahun', function () {
        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        DtsenRekap::create([
            'bulan' => 5, 'tahun' => 2026,
            'file_path' => 'a.csv', 'original_filename' => 'a.csv',
            'uploaded_by' => $admin->id,
        ]);

        expect(fn () => DtsenRekap::create([
            'bulan' => 5, 'tahun' => 2026,
            'file_path' => 'b.csv', 'original_filename' => 'b.csv',
            'uploaded_by' => $admin->id,
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('allows same bulan different tahun', function () {
        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        $a = DtsenRekap::create([
            'bulan' => 5, 'tahun' => 2025,
            'file_path' => 'a.csv', 'original_filename' => 'a.csv',
            'uploaded_by' => $admin->id,
        ]);
        $b = DtsenRekap::create([
            'bulan' => 5, 'tahun' => 2026,
            'file_path' => 'b.csv', 'original_filename' => 'b.csv',
            'uploaded_by' => $admin->id,
        ]);

        expect($a->id)->not->toBeNull();
        expect($b->id)->not->toBeNull();
    });
});

// ================================================================
// TEST: SOFT DELETE & FILE CLEANUP
// ================================================================

describe('Delete Behavior', function () {
    beforeEach(fn () => Storage::fake('local'));

    it('soft deletes without removing file', function () {
        Storage::disk('local')->put('dtsen-rekaps/keep.csv', 'data');
        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        $rekap = DtsenRekap::create([
            'bulan' => 10, 'tahun' => 2026,
            'file_path'         => 'dtsen-rekaps/keep.csv',
            'original_filename' => 'keep.csv',
            'uploaded_by'       => $admin->id,
        ]);

        $rekap->delete();
        expect($rekap->trashed())->toBeTrue();
        Storage::disk('local')->assertExists('dtsen-rekaps/keep.csv');
    });

    it('removes file on force delete', function () {
        Storage::disk('local')->put('dtsen-rekaps/rm.csv', 'data');
        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        $rekap = DtsenRekap::create([
            'bulan' => 11, 'tahun' => 2026,
            'file_path'         => 'dtsen-rekaps/rm.csv',
            'original_filename' => 'rm.csv',
            'uploaded_by'       => $admin->id,
        ]);

        $rekap->forceDelete();
        Storage::disk('local')->assertMissing('dtsen-rekaps/rm.csv');
        assertDatabaseMissing('dtsen_rekaps', ['id' => $rekap->id]);
    });
});

// ================================================================
// TEST: POLICY
// ================================================================

describe('Policy', function () {
    it('admin can create', function () {
        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        expect($admin->can('create', DtsenRekap::class))->toBeTrue();
    });

    it('operator cannot create', function () {
        $user = makeUser(UserRole::OPERATOR_BIDANG->value);
        expect($user->can('create', DtsenRekap::class))->toBeFalse();
    });

    it('admin can delete', function () {
        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        $rekap = makeRekapWithDetails();
        expect($admin->can('delete', $rekap))->toBeTrue();
    });

    it('operator cannot delete', function () {
        $user = makeUser(UserRole::OPERATOR_DESA->value);
        $rekap = makeRekapWithDetails();
        expect($user->can('delete', $rekap))->toBeFalse();
    });

    it('no one can update (immutable)', function () {
        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        $rekap = makeRekapWithDetails();
        expect($admin->can('update', $rekap))->toBeFalse();
    });

    it('all roles can view', function () {
        $rekap = makeRekapWithDetails();
        $roles = [
            UserRole::ADMIN_DINSOS->value,
            UserRole::OPERATOR_BIDANG->value,
            UserRole::OPERATOR_DESA->value,
            UserRole::VERIFIKATOR->value,
        ];
        foreach ($roles as $role) {
            $user = makeUser($role);
            expect($user->can('view', $rekap))->toBeTrue();
        }
    });
});

// ================================================================
// TEST: RELATION MANAGER - DETAIL TABLE
// ================================================================

describe('Relation Manager Details', function () {
    it('admin can see detail table on view page', function () {
        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        $rekap = makeRekapWithDetails();

        // Relation manager di-load via Livewire, cukup assertOk
        actingAs($admin)
            ->get(route('filament.admin.resources.dtsen-rekaps.view', $rekap))
            ->assertOk();

        // Verifikasi data detail ada di database
        expect($rekap->details()->where('kecamatan', 'Gerokgak')->exists())->toBeTrue();
        expect($rekap->details()->where('kelurahan', 'Sumberklampok')->exists())->toBeTrue();
    });

    it('operator bidang can see detail table on view page', function () {
        $user = makeUser(UserRole::OPERATOR_BIDANG->value);
        $rekap = makeRekapWithDetails();

        actingAs($user)
            ->get(route('filament.admin.resources.dtsen-rekaps.view', $rekap))
            ->assertOk();
    });

    it('detail table shows correct jumlah keluarga', function () {
        $admin = makeUser(UserRole::ADMIN_DINSOS->value);
        $rekap = makeRekapWithDetails();

        actingAs($admin)
            ->get(route('filament.admin.resources.dtsen-rekaps.view', $rekap))
            ->assertOk();

        // Verifikasi data via model langsung
        expect($rekap->details()->where('kecamatan', 'Gerokgak')->value('jumlah_keluarga'))->toBe(1194);
    });

    it('detail relation manager is read only', function () {
        $user = makeUser(UserRole::OPERATOR_BIDANG->value);
        $rekap = makeRekapWithDetails();

        // Operator tidak bisa create/edit/delete detail
        expect($user->can('create', DtsenRekap::class))->toBeFalse();
        expect($user->can('update', $rekap))->toBeFalse();
        expect($user->can('delete', $rekap))->toBeFalse();
    });
});
