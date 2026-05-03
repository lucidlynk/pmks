<?php

namespace App\Providers;

use App\Models\FamilyCard;
use App\Models\Institution;
use App\Models\PmksSubmission;
use App\Models\PsksSubmission;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Policies\FamilyCardPolicy;
use App\Policies\InstitutionPolicy;
use App\Policies\PmksSubmissionPolicy;
use App\Policies\PsksSubmissionPolicy;
use App\Policies\ResidentPolicy;
use App\Policies\SubmissionBatchPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        SubmissionBatch::class => SubmissionBatchPolicy::class,
        Resident::class        => ResidentPolicy::class,
        FamilyCard::class      => FamilyCardPolicy::class,
        Institution::class     => InstitutionPolicy::class,
        PmksSubmission::class  => PmksSubmissionPolicy::class,
        PsksSubmission::class  => PsksSubmissionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
