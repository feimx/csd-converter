<?php

namespace FeiMx\Csd;

use Illuminate\Support\ServiceProvider;

class CsdServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
