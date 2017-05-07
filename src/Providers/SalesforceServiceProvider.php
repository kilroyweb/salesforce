<?php

namespace KilroyWeb\Salesforce\Providers;

use Illuminate\Support\ServiceProvider;
use KilroyWeb\Salesforce\Salesforce;

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
            __DIR__.'/../Configuration/salesforce.php' => config_path('salesforce.php')
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerSalesforce();
    }

    public function registerSalesforce(){
        $this->app->bind('salesforce',function() {
            return new Salesforce();
        });
    }

    public function provides()
    {
        return array('salesforce', 'KilroyWeb\Salesforce');
    }



}
