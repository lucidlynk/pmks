<?php

namespace App\Http\Controllers;

use App\Models\BansosMember;
use App\Models\DtsenRekap;
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

        // PMKS per kecamatan
        $pmksPerKecamatan = PmksSubmission::whereHas('batch', fn ($q) => $q
            ->where('period_year', $tahun)
            ->where('status', 'approved')
        )
            ->with('village.kecamatan')
            ->get()
            ->groupBy(fn ($s) => $s->village->kecamatan->name ?? 'Lainnya')
            ->map->count()
            ->sortDesc();

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
        $dtsenRekap = DtsenRekap::with('details')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->first();

        $dtsenData = null;
        if ($dtsenRekap) {
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

        return view('welcome', compact(
            'pmksCategories',
            'psksCategories',
            'totalKecamatan',
            'totalDesa',
            'totalPmks',
            'totalPsks',
            'pmksPerKecamatan',
            'kisData',
            'dtsenData',
            'bansosData',
            'tahun',
        ));
    }
}
