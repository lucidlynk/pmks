<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use App\Models\PmksSubmission;
use App\Models\PsksSubmission;
use App\Models\Village;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
    private function getYear(Request $request): int
    {
        return (int) $request->get('tahun', now()->year);
    }

    /**
     * Ringkasan total PMKS & PSKS
     * GET /api/v1/statistik/ringkasan?tahun=2025
     */
    public function ringkasan(Request $request): JsonResponse
    {
        $year = $this->getYear($request);

        $totalPmks = PmksSubmission::whereHas('batch', fn ($q) =>
            $q->where('period_year', $year)->where('status', 'approved')
        )->count();

        $totalPsks = PsksSubmission::whereHas('batch', fn ($q) =>
            $q->where('period_year', $year)->where('status', 'approved')
        )->count();

        $totalPmksDraft = PmksSubmission::whereHas('batch', fn ($q) =>
            $q->where('period_year', $year)
        )->count();

        $totalPsksDraft = PsksSubmission::whereHas('batch', fn ($q) =>
            $q->where('period_year', $year)
        )->count();

        return response()->json([
            'status'    => 'success',
            'tahun'     => $year,
            'wilayah'   => 'Kabupaten Buleleng',
            'data'      => [
                'total_pmks'            => $totalPmks,
                'total_psks'            => $totalPsks,
                'total_pmks_semua_status' => $totalPmksDraft,
                'total_psks_semua_status' => $totalPsksDraft,
                'total_kecamatan'       => Kecamatan::active()->count(),
                'total_desa'            => Village::active()->count(),
            ],
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Data PMKS per kecamatan dan desa
     * GET /api/v1/statistik/pmks?tahun=2025&status=approved
     */
    public function pmks(Request $request): JsonResponse
    {
        $year   = $this->getYear($request);
        $status = $request->get('status', 'approved');

        $kecamatans = Kecamatan::active()
            ->with(['villages' => function ($q) {
                $q->active()->orderBy('name');
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($kecamatan) use ($year, $status) {
                $villages = $kecamatan->villages->map(function ($village) use ($year, $status) {
                    $query = PmksSubmission::where('village_id', $village->id)
                        ->whereHas('batch', fn ($q) =>
                            $q->where('period_year', $year)
                              ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
                        );

                    $total = $query->count();

                    // Per kategori
                    $perKategori = PmksSubmission::where('village_id', $village->id)
                        ->whereHas('batch', fn ($q) =>
                            $q->where('period_year', $year)
                              ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
                        )
                        ->with('category:id,code,name')
                        ->get()
                        ->groupBy('category_id')
                        ->map(fn ($items) => [
                            'kode'  => $items->first()->category?->code,
                            'nama'  => $items->first()->category?->name,
                            'total' => $items->count(),
                        ])
                        ->values();

                    return [
                        'id'           => $village->id,
                        'nama_desa'    => $village->name,
                        'tipe'         => $village->type,
                        'total_pmks'   => $total,
                        'per_kategori' => $perKategori,
                    ];
                });

                return [
                    'id'              => $kecamatan->id,
                    'nama_kecamatan'  => $kecamatan->name,
                    'total_pmks'      => $villages->sum('total_pmks'),
                    'total_desa'      => $villages->count(),
                    'desa'            => $villages,
                ];
            });

        return response()->json([
            'status'  => 'success',
            'tahun'   => $year,
            'filter_status' => $status,
            'wilayah' => 'Kabupaten Buleleng',
            'total_pmks' => $kecamatans->sum('total_pmks'),
            'data'    => $kecamatans,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Data PSKS per kecamatan dan desa
     * GET /api/v1/statistik/psks?tahun=2025&status=approved
     */
    public function psks(Request $request): JsonResponse
    {
        $year   = $this->getYear($request);
        $status = $request->get('status', 'approved');

        $kecamatans = Kecamatan::active()
            ->with(['villages' => function ($q) {
                $q->active()->orderBy('name');
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($kecamatan) use ($year, $status) {
                $villages = $kecamatan->villages->map(function ($village) use ($year, $status) {
                    $total = PsksSubmission::where('village_id', $village->id)
                        ->whereHas('batch', fn ($q) =>
                            $q->where('period_year', $year)
                              ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
                        )->count();

                    $totalPerson = PsksSubmission::where('village_id', $village->id)
                        ->where('subject_type', 'person')
                        ->whereHas('batch', fn ($q) =>
                            $q->where('period_year', $year)
                              ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
                        )->count();

                    $totalInstitution = PsksSubmission::where('village_id', $village->id)
                        ->where('subject_type', 'institution')
                        ->whereHas('batch', fn ($q) =>
                            $q->where('period_year', $year)
                              ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
                        )->count();

                    $perKategori = PsksSubmission::where('village_id', $village->id)
                        ->whereHas('batch', fn ($q) =>
                            $q->where('period_year', $year)
                              ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
                        )
                        ->with('category:id,code,name,subject_type')
                        ->get()
                        ->groupBy('category_id')
                        ->map(fn ($items) => [
                            'kode'         => $items->first()->category?->code,
                            'nama'         => $items->first()->category?->name,
                            'jenis_subjek' => $items->first()->category?->subject_type,
                            'total'        => $items->count(),
                        ])
                        ->values();

                    return [
                        'id'                => $village->id,
                        'nama_desa'         => $village->name,
                        'tipe'              => $village->type,
                        'total_psks'        => $total,
                        'total_individu'    => $totalPerson,
                        'total_lembaga'     => $totalInstitution,
                        'per_kategori'      => $perKategori,
                    ];
                });

                return [
                    'id'             => $kecamatan->id,
                    'nama_kecamatan' => $kecamatan->name,
                    'total_psks'     => $villages->sum('total_psks'),
                    'total_desa'     => $villages->count(),
                    'desa'           => $villages,
                ];
            });

        return response()->json([
            'status'     => 'success',
            'tahun'      => $year,
            'filter_status' => $status,
            'wilayah'    => 'Kabupaten Buleleng',
            'total_psks' => $kecamatans->sum('total_psks'),
            'data'       => $kecamatans,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Ringkasan per kecamatan
     * GET /api/v1/statistik/kecamatan?tahun=2025
     */
    public function perKecamatan(Request $request): JsonResponse
    {
        $year = $this->getYear($request);

        $data = Kecamatan::active()
            ->orderBy('name')
            ->get()
            ->map(function ($kecamatan) use ($year) {
                $villageIds = $kecamatan->villages()->active()->pluck('id');

                $totalPmks = PmksSubmission::whereIn('village_id', $villageIds)
                    ->whereHas('batch', fn ($q) =>
                        $q->where('period_year', $year)->where('status', 'approved')
                    )->count();

                $totalPsks = PsksSubmission::whereIn('village_id', $villageIds)
                    ->whereHas('batch', fn ($q) =>
                        $q->where('period_year', $year)->where('status', 'approved')
                    )->count();

                return [
                    'id'             => $kecamatan->id,
                    'nama_kecamatan' => $kecamatan->name,
                    'kode'           => $kecamatan->code,
                    'total_desa'     => $kecamatan->villages()->active()->count(),
                    'total_pmks'     => $totalPmks,
                    'total_psks'     => $totalPsks,
                    'total'          => $totalPmks + $totalPsks,
                ];
            });

        return response()->json([
            'status'     => 'success',
            'tahun'      => $year,
            'wilayah'    => 'Kabupaten Buleleng',
            'total_pmks' => $data->sum('total_pmks'),
            'total_psks' => $data->sum('total_psks'),
            'data'       => $data,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Ringkasan per desa dalam kecamatan tertentu
     * GET /api/v1/statistik/desa/{kecamatan_id}?tahun=2025
     */
    public function perDesa(Request $request, ?int $kecamatanId = null): JsonResponse
    {
        $year = $this->getYear($request);

        $query = Village::active()->with('kecamatan:id,name');

        if ($kecamatanId) {
            $query->where('kecamatan_id', $kecamatanId);
        }

        $data = $query->orderBy('name')->get()->map(function ($village) use ($year) {
            $totalPmks = PmksSubmission::where('village_id', $village->id)
                ->whereHas('batch', fn ($q) =>
                    $q->where('period_year', $year)->where('status', 'approved')
                )->count();

            $totalPsks = PsksSubmission::where('village_id', $village->id)
                ->whereHas('batch', fn ($q) =>
                    $q->where('period_year', $year)->where('status', 'approved')
                )->count();

            return [
                'id'             => $village->id,
                'nama_desa'      => $village->name,
                'tipe'           => $village->type,
                'kecamatan_id'   => $village->kecamatan_id,
                'nama_kecamatan' => $village->kecamatan?->name,
                'total_pmks'     => $totalPmks,
                'total_psks'     => $totalPsks,
                'total'          => $totalPmks + $totalPsks,
            ];
        });

        return response()->json([
            'status'     => 'success',
            'tahun'      => $year,
            'wilayah'    => $kecamatanId
                ? Kecamatan::find($kecamatanId)?->name . ', Buleleng'
                : 'Kabupaten Buleleng',
            'total_pmks' => $data->sum('total_pmks'),
            'total_psks' => $data->sum('total_psks'),
            'data'       => $data,
            'generated_at' => now()->toIso8601String(),
        ]);
    }
}
