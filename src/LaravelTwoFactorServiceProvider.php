<?php

namespace BuckhamDuffy\LaravelTwoFactor;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelTwoFactorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-two-factor')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web')
            ->hasRoute('api')
            ->hasMigration('add_two_factor_column_to_users');
    }
}
