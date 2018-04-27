<?php

namespace bskl\LaravelMpSms;

use bskl\LaravelMpSms\MpSms;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = false;

    /**
     * @var string
     */
    protected $configName = 'mp-sms';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/' . $this->configName . '.php';

        $this->mergeConfigFrom($configPath, $this->configName);

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
        $configPath = __DIR__ . '/../config/' . $this->configName . '.php';

        $this->publishes([$configPath => config_path($this->configName . '.php')], 'config');
    }

}
