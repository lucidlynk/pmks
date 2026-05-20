<?php

namespace App\Providers;

use App\Models\DtsenRekap;
use App\Observers\DtsenRekapObserver;
use App\Policies\DtsenRekapPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class DtsenRekapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        DtsenRekap::observe(DtsenRekapObserver::class);
        Gate::policy(DtsenRekap::class, DtsenRekapPolicy::class);
    }
}
