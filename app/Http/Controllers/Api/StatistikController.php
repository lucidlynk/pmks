<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BansosMember;
use App\Models\DtsenRekap;
use App\Models\Kecamatan;
use App\Models\KisRekap;
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

    // ================================================================
    // PMKS & PSKS (sudah ada, tidak berubah)
    // ================================================================

    public function ringkasan(Request $request): JsonResponse
    {
        $year = $this->getYear($request);

        $totalPmks = PmksSubmission::whereHas('batch', fn ($q) =>
            $q->where('period_year', $year)->where('status', 'approved')
        )->count();

        $totalPsks = PsksSubmission::whereHas('batch', fn ($q) =>
            $q->where('period_year', $year)->where('status', 'approved')
        )->count();

        return response()->json([
            'success'      => true,
            'tahun'        => $year,
            'wilayah'      => 'Kabupaten Buleleng',
            'data'         => [
                'total_pmks'      => $totalPmks,
                'total_psks'      => $totalPsks,
                'total_kecamatan' => Kecamatan::active()->count(),
                'total_desa'      => Village::active()->count(),
            ],
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function pmks(Request $request): JsonResponse
    {
        $year   = $this->getYear($request);
        $status = $request->get('status', 'approved');

        $kecamatans = Kecamatan::active()
            ->with(['villages' => fn ($q) => $q->active()->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(function ($kecamatan) use ($year, $status) {
                $villages = $kecamatan->villages->map(function ($village) use ($year, $status) {
                    $total = PmksSubmission::where('village_id', $village->id)
                        ->whereHas('batch', fn ($q) =>
                            $q->where('period_year', $year)
                              ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
                        )->count();

                    return [
                        'id'         => $village->id,
                        'nama_desa'  => $village->name,
                        'tipe'       => $village->type,
                        'total_pmks' => $total,
                    ];
                });

                return [
                    'id'             => $kecamatan->id,
                    'nama_kecamatan' => $kecamatan->name,
                    'total_pmks'     => $villages->sum('total_pmks'),
                    'desa'           => $villages,
                ];
            });

        return response()->json([
            'success'      => true,
            'tahun'        => $year,
            'status_filter'=> $status,
            'wilayah'      => 'Kabupaten Buleleng',
            'total_pmks'   => $kecamatans->sum('total_pmks'),
            'data'         => $kecamatans,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function psks(Request $request): JsonResponse
    {
        $year   = $this->getYear($request);
        $status = $request->get('status', 'approved');

        $kecamatans = Kecamatan::active()
            ->with(['villages' => fn ($q) => $q->active()->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(function ($kecamatan) use ($year, $status) {
                $villages = $kecamatan->villages->map(function ($village) use ($year, $status) {
                    $total = PsksSubmission::where('village_id', $village->id)
                        ->whereHas('batch', fn ($q) =>
                            $q->where('period_year', $year)
                              ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
                        )->count();

                    return [
                        'id'         => $village->id,
                        'nama_desa'  => $village->name,
                        'tipe'       => $village->type,
                        'total_psks' => $total,
                    ];
                });

                return [
                    'id'             => $kecamatan->id,
                    'nama_kecamatan' => $kecamatan->name,
                    'total_psks'     => $villages->sum('total_psks'),
                    'desa'           => $villages,
                ];
            });

        return response()->json([
            'success'      => true,
            'tahun'        => $year,
            'status_filter'=> $status,
            'wilayah'      => 'Kabupaten Buleleng',
            'total_psks'   => $kecamatans->sum('total_psks'),
            'data'         => $kecamatans,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function perKecamatan(Request $request): JsonResponse
    {
        $year = $this->getYear($request);

        $data = Kecamatan::active()->orderBy('name')->get()
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
                ];
            });

        return response()->json([
            'success'      => true,
            'tahun'        => $year,
            'wilayah'      => 'Kabupaten Buleleng',
            'total_pmks'   => $data->sum('total_pmks'),
            'total_psks'   => $data->sum('total_psks'),
            'data'         => $data,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function perDesa(Request $request, ?int $kecamatanId = null): JsonResponse
    {
        $year  = $this->getYear($request);
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
                'nama_kecamatan' => $village->kecamatan?->name,
                'total_pmks'     => $totalPmks,
                'total_psks'     => $totalPsks,
            ];
        });

        return response()->json([
            'success'      => true,
            'tahun'        => $year,
            'wilayah'      => $kecamatanId
                ? (Kecamatan::find($kecamatanId)?->name . ', Buleleng')
                : 'Kabupaten Buleleng',
            'total_pmks'   => $data->sum('total_pmks'),
            'total_psks'   => $data->sum('total_psks'),
            'data'         => $data,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    // ================================================================
    // DTSEN
    // ================================================================

    public function dtsen(Request $request): JsonResponse
    {
        // Ambil rekap DTSEN terbaru
        $rekap = DtsenRekap::with('details')
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->first();

        if (!$rekap) {
            return response()->json([
                'success' => true,
                'message' => 'Belum ada data DTSEN',
                'data'    => null,
                'generated_at' => now()->toIso8601String(),
            ]);
        }

        $bulanLabel = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
        ];

        return response()->json([
            'success'  => true,
            'periode'  => ($bulanLabel[$rekap->bulan] ?? $rekap->bulan) . ' ' . $rekap->tahun,
            'wilayah'  => 'Kabupaten Buleleng',
            'data'     => [
                'total_keluarga'    => $rekap->details->sum('jumlah_keluarga'),
                'total_jiwa'        => $rekap->details->sum('jumlah_individu'),
                'per_desil'         => $rekap->details->map(fn ($d) => [
                    'desil'           => $d->desil,
                    'jumlah_keluarga' => $d->jumlah_keluarga,
                    'jumlah_jiwa'     => $d->jumlah_individu,
                ])->values(),
            ],
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    // ================================================================
    // KIS
    // ================================================================

    public function kis(Request $request): JsonResponse
    {
        $year  = $this->getYear($request);
        $bulan = $request->get('bulan');

        $query = KisRekap::forYear($year)->orderBy('periode_bulan');

        if ($bulan) {
            $query->where('periode_bulan', (int) $bulan);
        }

        $rekaps = $query->get();

        $bulanLabel = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
        ];

        return response()->json([
            'success' => true,
            'tahun'   => $year,
            'wilayah' => 'Kabupaten Buleleng',
            'data'    => $rekaps->map(fn ($r) => [
                'bulan'         => $r->periode_bulan,
                'nama_bulan'    => $bulanLabel[$r->periode_bulan] ?? $r->periode_bulan,
                'pbi_apbd'      => $r->pbi_apbd,
                'pbi_apbn'      => $r->pbi_apbn,
                'ppu'           => $r->ppu,
                'pbpu'          => $r->pbpu,
                'bp'            => $r->bp,
                'total'         => $r->total,
            ])->values(),
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    // ================================================================
    // BANSOS
    // ================================================================

    public function bansos(Request $request): JsonResponse
    {
        $jenis     = $request->get('jenis', 'pkh');
        $triwulan  = (int) $request->get('triwulan', 1);
        $year      = $this->getYear($request);

        // Validasi input
        if (!in_array($jenis, ['pkh', 'sembako'])) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter jenis harus pkh atau sembako',
            ], 422);
        }

        if ($triwulan < 1 || $triwulan > 4) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter triwulan harus antara 1-4',
            ], 422);
        }

        // Agregat per kecamatan per desa per status
        $raw = BansosMember::where('jenis_bansos', $jenis)
            ->where('triwulan', $triwulan)
            ->where('tahun', $year)
            ->selectRaw('kec_name, kel_name, status_bansos, COUNT(*) as jumlah')
            ->groupBy('kec_name', 'kel_name', 'status_bansos')
            ->orderBy('kec_name')
            ->orderBy('kel_name')
            ->get();

        // Reshape
        $data = [];
        foreach ($raw as $row) {
            $kec  = $row->kec_name ?? 'Tidak Diketahui';
            $kel  = $row->kel_name ?? 'Tidak Diketahui';
            $stat = $row->status_bansos;

            if (!isset($data[$kec])) $data[$kec] = [];
            if (!isset($data[$kec][$kel])) {
                $data[$kec][$kel] = [
                    'sudah_si'        => 0,
                    'sudah_salur'     => 0,
                    'sudah_transaksi' => 0,
                ];
            }
            $data[$kec][$kel][$stat] = $row->jumlah;
        }

        // Format response
        $formatted = collect($data)->map(function ($desas, $kec) {
            $desaList = collect($desas)->map(fn ($stat, $kel) => array_merge(
                ['nama_desa' => $kel], $stat
            ))->values();

            return [
                'nama_kecamatan'  => $kec,
                'total_sudah_si'  => $desaList->sum('sudah_si'),
                'total_sudah_salur'=> $desaList->sum('sudah_salur'),
                'total_sudah_transaksi' => $desaList->sum('sudah_transaksi'),
                'desa'            => $desaList,
            ];
        })->values();

        $triwulanLabel = [1=>'TW1 (Jan-Mar)', 2=>'TW2 (Apr-Jun)', 3=>'TW3 (Jul-Sep)', 4=>'TW4 (Okt-Des)'];

        return response()->json([
            'success'    => true,
            'jenis'      => strtoupper($jenis),
            'triwulan'   => $triwulan,
            'periode'    => ($triwulanLabel[$triwulan] ?? 'TW'.$triwulan) . ' ' . $year,
            'tahun'      => $year,
            'wilayah'    => 'Kabupaten Buleleng',
            'total_sudah_si'        => $formatted->sum('total_sudah_si'),
            'total_sudah_salur'     => $formatted->sum('total_sudah_salur'),
            'total_sudah_transaksi' => $formatted->sum('total_sudah_transaksi'),
            'data'       => $formatted,
            'generated_at' => now()->toIso8601String(),
        ]);
    }
}
