<?php

namespace Rud99\SberSbp;

use Illuminate\Support\ServiceProvider;

class SberSbpServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'rud99');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'rud99');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
//        $this->mergeConfigFrom(__DIR__ . '/../config/sber-sbp.php', 'sber-sbp');

        // Register the service the package provides.
        $this->app->singleton('sber-sbp', function ($app) {
            $client =  new Client(
                env('SBER_SBP_TERMINAL_ID'),
                env('SBER_SBP_MEMBER_ID'),
                env('SBER_SBP_CLIENT_ID'),
                env('SBER_SBP_CLIENT_SECRET'),
                env('SBER_SBP_CERT_PATH'),
                env('SBER_SBP_CERT_PASSWORD'),
                env('SBER_SBP_IS_PRODUCTION', false),
            );
            $client->setCache(new LaravelCacheAdapter());
            return $client;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['sber-sbp'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        /*$this->publishes([
            __DIR__ . '/../config/sber-sbp.php' => config_path('sber-sbp.php'),
        ], 'sber-sbp.config');*/

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/rud99'),
        ], 'sber-sbp.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/rud99'),
        ], 'sber-sbp.assets');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/rud99'),
        ], 'sber-sbp.lang');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
