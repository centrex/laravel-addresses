<?php

declare(strict_types=1);

namespace Centrex\LaravelAddresses;

use Illuminate\Support\ServiceProvider;

class LaravelAddressesServiceProvider extends ServiceProvider
{
    /** Bootstrap the application services. */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-addresses');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-addresses');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-addresses.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-addresses'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-addresses'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-addresses'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /** Register the application services. */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-addresses');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-addresses', function () {
            return new LaravelAddresses;
        });
    }
}
