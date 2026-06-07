<?php

namespace App\Http\Controllers;

use App\Models\BansosMember;
use App\Models\DtsenRekap;
use App\Models\DtsenRekapDetail;
use App\Models\Kecamatan;
use App\Models\KisRekap;
use App\Models\PmksSubmission;
use App\Models\PsksSubmission;
use App\Models\PmksCategory;
use App\Models\PsksCategory;
use App\Models\Village;

class WelcomeController extends Controller
{
    public function index()
    {
        $tahun = now()->year;

        // ── Kategori ──────────────────────────────────────────────
        $pmksCategories = PmksCategory::active()->orderBy('code')->get();
        $psksCategories = PsksCategory::active()->orderBy('code')->get();

        // ── Wilayah ───────────────────────────────────────────────
        $totalKecamatan = Kecamatan::active()->count();
        $totalDesa      = Village::active()->count();

        // ── PMKS & PSKS tahun ini ─────────────────────────────────
        $totalPmks = PmksSubmission::whereHas('batch', fn ($q) => $q
            ->where('period_year', $tahun)
            ->where('status', 'approved')
        )->count();

        $totalPsks = PsksSubmission::whereHas('batch', fn ($q) => $q
            ->where('period_year', $tahun)
            ->where('status', 'approved')
        )->count();

        // ── PMKS per village (1 query) ────────────────────────────
        $pmksPerVillage = PmksSubmission::query()
            ->join('submission_batches', 'pmks_submissions.batch_id', '=', 'submission_batches.id')
            ->where('submission_batches.period_year', $tahun)
            ->where('submission_batches.status', 'approved')
            ->whereNull('pmks_submissions.deleted_at')
            ->selectRaw('pmks_submissions.village_id, COUNT(*) as total')
            ->groupBy('pmks_submissions.village_id')
            ->pluck('total', 'village_id');

        // ── PSKS per village (1 query) ────────────────────────────
        $psksPerVillage = PsksSubmission::query()
            ->join('submission_batches', 'psks_submissions.batch_id', '=', 'submission_batches.id')
            ->where('submission_batches.period_year', $tahun)
            ->where('submission_batches.status', 'approved')
            ->whereNull('psks_submissions.deleted_at')
            ->selectRaw('psks_submissions.village_id, COUNT(*) as total')
            ->groupBy('psks_submissions.village_id')
            ->pluck('total', 'village_id');

        // ── DTSEN per kelurahan (1 query, rekap terbaru) ──────────
        $dtsenTerbaru = DtsenRekap::orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->first();

        $dtsenPerKelurahan = collect();
        if ($dtsenTerbaru) {
            $dtsenPerKelurahan = DtsenRekapDetail::where('dtsen_rekap_id', $dtsenTerbaru->id)
                ->get()
                ->keyBy('kelurahan');
        }

        // ── Data per kecamatan + desa (accordion) ─────────────────
        $kecamatanData = Kecamatan::active()
            ->with(['villages' => fn ($q) => $q->active()->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(function ($kecamatan) use ($pmksPerVillage, $psksPerVillage, $dtsenPerKelurahan) {
                $desas = $kecamatan->villages->map(function ($village) use ($pmksPerVillage, $psksPerVillage, $dtsenPerKelurahan) {
                    $dtsen = $dtsenPerKelurahan->get($village->name);
                    return [
                        'nama'           => $village->name,
                        'tipe'           => $village->type,
                        'total_pmks'     => $pmksPerVillage[$village->id] ?? 0,
                        'total_psks'     => $psksPerVillage[$village->id] ?? 0,
                        'dtsen_keluarga' => $dtsen?->jumlah_keluarga ?? 0,
                        'dtsen_individu' => $dtsen?->jumlah_individu ?? 0,
                    ];
                });

                return [
                    'nama'           => $kecamatan->name,
                    'total_desa'     => $desas->count(),
                    'total_pmks'     => $desas->sum('total_pmks'),
                    'total_psks'     => $desas->sum('total_psks'),
                    'dtsen_keluarga' => $desas->sum('dtsen_keluarga'),
                    'dtsen_individu' => $desas->sum('dtsen_individu'),
                    'desas'          => $desas,
                ];
            });

        // ── KIS ───────────────────────────────────────────────────
        $kisRekap = KisRekap::orderBy('periode_tahun', 'desc')
            ->orderBy('periode_bulan', 'desc')
            ->first();

        $kisData = null;
        if ($kisRekap) {
            $kisData = [
                'periode'  => $kisRekap->periode_bulan . '/' . $kisRekap->periode_tahun,
                'total'    => $kisRekap->total,
                'pbi_apbd' => $kisRekap->pbi_apbd,
                'pbi_apbn' => $kisRekap->pbi_apbn,
                'ppu'      => $kisRekap->ppu,
                'pbpu'     => $kisRekap->pbpu,
                'bp'       => $kisRekap->bp,
            ];
        }

        // ── DTSEN ─────────────────────────────────────────────────
        $dtsenRekap = $dtsenTerbaru;
        $dtsenData  = null;
        if ($dtsenRekap) {
            $dtsenRekap->load('details');
            $d = $dtsenRekap->details;
            $dtsenData = [
                'periode'         => $dtsenRekap->bulan . '/' . $dtsenRekap->tahun,
                'total_keluarga'  => $d->sum('jumlah_keluarga'),
                'total_individu'  => $d->sum('jumlah_individu'),
                'desil1_keluarga' => $d->sum('desil1_keluarga'),
                'desil1_individu' => $d->sum('desil1_individu'),
                'desil2_keluarga' => $d->sum('desil2_keluarga'),
                'desil2_individu' => $d->sum('desil2_individu'),
                'desil3_keluarga' => $d->sum('desil3_keluarga'),
                'desil3_individu' => $d->sum('desil3_individu'),
                'desil4_keluarga' => $d->sum('desil4_keluarga'),
                'desil4_individu' => $d->sum('desil4_individu'),
                'desil5_keluarga' => $d->sum('desil5_keluarga'),
                'desil5_individu' => $d->sum('desil5_individu'),
            ];
        }

        // ── Bansos ────────────────────────────────────────────────
        $bansosData = BansosMember::selectRaw(
            'jenis_bansos, triwulan, tahun, count(*) as total'
        )
            ->groupBy('jenis_bansos', 'triwulan', 'tahun')
            ->orderBy('tahun', 'desc')
            ->orderBy('triwulan', 'desc')
            ->get()
            ->groupBy('jenis_bansos');

        // pmksPerKecamatan tetap ada untuk backward compat test
        $pmksPerKecamatan = $kecamatanData->pluck('total_pmks', 'nama')->sortDesc();

        return view('welcome', compact(
            'pmksCategories',
            'psksCategories',
            'totalKecamatan',
            'totalDesa',
            'totalPmks',
            'totalPsks',
            'pmksPerKecamatan',
            'kecamatanData',
            'kisData',
            'dtsenData',
            'bansosData',
            'tahun',
        ));
    }
}
