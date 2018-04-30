<?php

namespace Bskl\MpSms;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * @var bool
     */

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/mp-sms.php', 'mp-sms'
        );

        $this->app->bind(MpSms::class, MpSms::class);

        $this->app->singleton('mpsms', function ($app) {
            return new MpSms();
        });

        $this->app->alias('mpsms', MpSms::class);
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/mp-sms.php' => config_path('mp-sms.php'),
            ], 'config');
        }
    }
}
