<?php

namespace Songyz\Providers;

use Illuminate\Validation\ValidationServiceProvider as FrameValidationServiceProvider;
use Songyz\Validator\Validator;

class ValidationServiceProvider extends FrameValidationServiceProvider
{
    /**
     * Register the validation factory.
     *
     * @return void
     */
    protected function registerValidationFactory()
    {
        $this->app->singleton('validator', function ($app) {
            $validator = new Validator($app['translator'], $app);

            // The validation presence verifier is responsible for determining the existence of
            // values in a given data collection which is typically a relational database or
            // other persistent data stores. It is used to check for "uniqueness" as well.
            if (isset($app['db'], $app['validation.presence'])) {
                $validator->setPresenceVerifier($app['validation.presence']);
            }

            $validator->initExtendsRule();

            return $validator;
        });
    }


}
