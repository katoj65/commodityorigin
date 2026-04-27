<?php

namespace App\Providers;

use App\Models\Batch;
use App\Models\Harvest;
use App\Models\Season;
use App\Policies\BatchPolicy;
use App\Policies\HarvestPolicy;
use App\Policies\SeasonPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Batch::class, BatchPolicy::class);
        Gate::policy(Harvest::class, HarvestPolicy::class);
        Gate::policy(Season::class, SeasonPolicy::class);
    }
}
