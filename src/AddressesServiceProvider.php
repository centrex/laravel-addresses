<?php

declare(strict_types = 1);

namespace Centrex\Addresses;

use Illuminate\Support\ServiceProvider;

class AddressesServiceProvider extends ServiceProvider
{
    /** Bootstrap the application services. */
    public function boot(): void
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'addresses');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'addresses');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('laravel-addresses.php'),
            ], 'addresses-config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/addresses'),
            ], 'addresses-views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/addresses'),
            ], 'addresses-assets');*/

            // Publishing the translation files.
            $this->publishes([
                __DIR__ . '/../resources/lang' => resource_path('lang/vendor/addresses'),
            ], 'addresses-lang');

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /** Register the application services. */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'laravel-addresses');
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'addresses');

        $this->app->singleton(Addresses::class, fn (): Addresses => new Addresses());
        $this->app->alias(Addresses::class, 'addresses');
    }
}
