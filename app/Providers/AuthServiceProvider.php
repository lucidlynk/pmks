<?php

namespace App\Providers;

use App\Models\DinasSurat;
use App\Models\FamilyCard;
use App\Models\Institution;
use App\Models\KisPbiApbdImport;
use App\Models\KisRekap;
use App\Models\PmksSubmission;
use App\Models\PsksSubmission;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Policies\DinasSuratPolicy;
use App\Policies\FamilyCardPolicy;
use App\Policies\InstitutionPolicy;
use App\Policies\KisPbiApbdImportPolicy;
use App\Policies\KisRekapPolicy;
use App\Policies\PmksSubmissionPolicy;
use App\Policies\PsksSubmissionPolicy;
use App\Policies\ResidentPolicy;
use App\Policies\SubmissionBatchPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        SubmissionBatch::class  => SubmissionBatchPolicy::class,
        Resident::class         => ResidentPolicy::class,
        FamilyCard::class       => FamilyCardPolicy::class,
        Institution::class      => InstitutionPolicy::class,
        PmksSubmission::class   => PmksSubmissionPolicy::class,
        PsksSubmission::class   => PsksSubmissionPolicy::class,
        KisRekap::class         => KisRekapPolicy::class,
        KisPbiApbdImport::class => KisPbiApbdImportPolicy::class,
        DinasSurat::class       => DinasSuratPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
