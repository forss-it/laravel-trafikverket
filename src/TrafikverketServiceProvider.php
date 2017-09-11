<?php

namespace Dialect\Trafikverket;

use Illuminate\Support\ServiceProvider;

class TrafikverketServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
	    $configPath = __DIR__.'/../config/trafikverket.php';
	    $this->publishes([$configPath => config_path('trafikverket.php')], 'config');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
	    $this->app->bind('trafikverket', Trafikverket::class);
    }
}