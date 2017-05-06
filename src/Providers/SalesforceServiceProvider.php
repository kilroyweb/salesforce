<?php

namespace KilroyWeb\Salesforce\Providers;

use Illuminate\Support\ServiceProvider;

class SalesforceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                //\KilroyWeb\Salesforce\Commands\...::class,
            ]);
        }
        //
        $this->publishes([
            __DIR__.'/../Configuration/Templates/salesforce.php' => config_path('salesforce.php')
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
