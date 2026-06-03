<?php

namespace App\Providers;

use App\Models\Batch;
use App\Models\Harvest;
use App\Models\Lot;
use App\Models\LotRequest;
use App\Models\Season;
use App\Policies\BatchPolicy;
use App\Policies\HarvestPolicy;
use App\Policies\LotPolicy;
use App\Policies\LotRequestPolicy;
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
        Gate::policy(Lot::class, LotPolicy::class);
        Gate::policy(LotRequest::class, LotRequestPolicy::class);
        Gate::policy(Season::class, SeasonPolicy::class);
    }
}
