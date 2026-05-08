<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use App\Models\PmksCategory;
use App\Models\PsksCategory;
use App\Models\Village;

class WelcomeController extends Controller
{
    public function index()
    {
        $pmksCategories = PmksCategory::active()
            ->orderBy('code')
            ->get();

        $psksCategories = PsksCategory::active()
            ->orderBy('code')
            ->get();

        $totalKecamatan = Kecamatan::active()->count();
        $totalDesa      = Village::active()->count();

        return view('welcome', compact(
            'pmksCategories',
            'psksCategories',
            'totalKecamatan',
            'totalDesa',
        ));
    }
}
