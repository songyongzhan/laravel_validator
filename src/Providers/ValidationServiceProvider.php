<?php

namespace Songyz\Providers;

use Illuminate\Validation\ValidationServiceProvider as FrameValidationServiceProvider;
use Songyz\Validator\Validator;

/**
 * 验证器服务提供 - 重写laravel 系统自带的 validator
 * Class ValidationServiceProvider
 * @package Songyz\Providers
 * @author songyz <574482856@qq.com>
 * @date 2020/5/10 20:02
 */
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
