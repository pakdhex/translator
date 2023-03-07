<?php

namespace Pakdhe\Translator;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->app->bind('pakdhe', function ($app) {
            return new YourPackage();
        });
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}