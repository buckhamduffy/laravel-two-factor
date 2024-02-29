<?php

namespace BuckhamDuffy\LaravelTwoFactor\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BuckhamDuffy\LaravelTwoFactor\LaravelTwoFactor
 */
class LaravelTwoFactor extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \BuckhamDuffy\LaravelTwoFactor\LaravelTwoFactor::class;
    }
}
