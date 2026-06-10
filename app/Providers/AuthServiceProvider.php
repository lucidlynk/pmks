<?php

namespace App\Providers;

use App\Models\BansosImport;
use App\Models\DinasSurat;
use App\Models\FamilyCard;
use App\Models\Institution;
use App\Models\KisPbiApbdImport;
use App\Models\KisRekap;
use App\Models\PmksImport;
use App\Models\PmksSubmission;
use App\Models\PsksImport;
use App\Models\PsksSubmission;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Policies\BansosImportPolicy;
use App\Policies\DinasSuratPolicy;
use App\Policies\FamilyCardPolicy;
use App\Policies\InstitutionPolicy;
use App\Policies\KisPbiApbdImportPolicy;
use App\Policies\KisRekapPolicy;
use App\Policies\PmksImportPolicy;
use App\Policies\PmksSubmissionPolicy;
use App\Policies\PsksImportPolicy;
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
        PmksImport::class       => PmksImportPolicy::class,
        PsksImport::class       => PsksImportPolicy::class,
        KisRekap::class         => KisRekapPolicy::class,
        KisPbiApbdImport::class => KisPbiApbdImportPolicy::class,
        DinasSurat::class       => DinasSuratPolicy::class,
        BansosImport::class     => BansosImportPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
