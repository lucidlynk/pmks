<?php

namespace App\Providers;

use App\Models\FamilyCard;
use App\Models\Institution;
use App\Models\PmksSubmission;
use App\Models\PsksSubmission;
use App\Models\Resident;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Observers\AuditLogObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (config('app.env') === 'production' ||
            request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }

        // Map string 'person'/'institution' di DB ke class Eloquent yang benar
        Relation::morphMap([
            'person'      => Resident::class,
            'institution' => Institution::class,
        ]);

        User::observe(AuditLogObserver::class);
        FamilyCard::observe(AuditLogObserver::class);
        Resident::observe(AuditLogObserver::class);
        Institution::observe(AuditLogObserver::class);
        SubmissionBatch::observe(AuditLogObserver::class);
        PmksSubmission::observe(AuditLogObserver::class);
        PsksSubmission::observe(AuditLogObserver::class);
    }
}
