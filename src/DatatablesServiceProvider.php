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
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

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
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'datatables');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'datatables');
        $this->mergeConfigFrom(__DIR__.'/../config/datatables.php', 'datatables');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

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
            __DIR__.'/../resources/js' => base_path('resources/js'),
            __DIR__.'/../resources/sass' => base_path('resources/sass'),
            __DIR__.'/../resources/css' => base_path('resources/css'),
        ], 'datatables.assets');

        // Publishing the translation files.
        $this->publishes([
            __DIR__.'/../resources/lang' => base_path('resources/lang'),
        ], 'datatables.lang');

        // Registering package commands.
        // $this->commands([]);
    }
}
