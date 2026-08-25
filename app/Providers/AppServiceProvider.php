<?php

namespace App\Providers;

use App\Models\AgriculturalInput;
use App\Models\Batch;
use App\Models\FarmCollection;
use App\Models\GalleryImage;
use App\Models\Harvest;
use App\Models\Lot;
use App\Models\LotRequest;
use App\Models\Season;
use App\Policies\AgriculturalInputPolicy;
use App\Policies\BatchPolicy;
use App\Policies\FarmCollectionPolicy;
use App\Policies\GalleryImagePolicy;
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
        Gate::policy(AgriculturalInput::class, AgriculturalInputPolicy::class);
        Gate::policy(Batch::class, BatchPolicy::class);
        Gate::policy(FarmCollection::class, FarmCollectionPolicy::class);
        Gate::policy(GalleryImage::class, GalleryImagePolicy::class);
        Gate::policy(Harvest::class, HarvestPolicy::class);
        Gate::policy(Lot::class, LotPolicy::class);
        Gate::policy(LotRequest::class, LotRequestPolicy::class);
        Gate::policy(Season::class, SeasonPolicy::class);
    }
}
