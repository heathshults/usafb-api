<?php

namespace App\Providers;

use App\Http\Services\ApiKeyService;
use Illuminate\Support\ServiceProvider;

class ApiKeyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // intentially left blank.
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ApiKeyService::class, function () {
            return new ApiKeyService();
        });

        $this->app->alias(ApiKeyService::class, 'ApiKey');
    }
}
