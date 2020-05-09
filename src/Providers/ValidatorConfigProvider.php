<?php

namespace Songyz\Providers;

use Illuminate\Support\ServiceProvider;
use Songyz\Command\GeneratorValidatorRequestCommand;

class ValidatorConfigProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/songyz_validator.php';

        $this->publishes([
            $configPath => config_path('songyz_validator.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge configs
        $this->mergeConfigFrom(
            __DIR__ . '/../config/songyz_validator.php',
            'songyz_validator'
        );

        $this->app->singleton('songyz.validator.generator', function ($app) {
            return new GeneratorValidatorRequestCommand();
        });
        $this->commands([
            'songyz.validator.generator'
        ]);

    }
}
