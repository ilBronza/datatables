<?php

namespace IlBronza\Datatables;

use Illuminate\Support\ServiceProvider;

class DatatablesServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'IlBronza');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'datatables');
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
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/datatables.php', 'datatables');

        // Register the service the package provides.
        $this->app->singleton('datatables', function ($app) {
            return new Datatables;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['datatables'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/datatables.php' => config_path('datatables.php'),
        ], 'datatables.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/IlBronza'),
        ], 'datatables.views');*/

        // $this->publishes([
        //     __DIR__.'/path/to/assets' => public_path('vendor/IlBronza'),
        // ], 'public');

        // Publishing assets.
        $this->publishes([
            __DIR__.'/../resources/assets' => base_path('public/datatables'),
        ], 'datatables.assets');

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/IlBronza'),
        ], 'datatables.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
