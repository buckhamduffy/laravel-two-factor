<?php

namespace BuckhamDuffy\LaravelTwoFactor\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Eloquent\Factories\Factory;
use BuckhamDuffy\LaravelTwoFactor\LaravelTwoFactorServiceProvider;

class TestCase extends Orchestra
{
	protected function setUp(): void
	{
		parent::setUp();

		Factory::guessFactoryNamesUsing(
			fn (string $modelName) => 'BuckhamDuffy\\LaravelTwoFactor\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
		);
	}

	protected function getPackageProviders($app)
	{
		return [
			LaravelTwoFactorServiceProvider::class,
		];
	}

	public function getEnvironmentSetUp($app): void
	{
		config()->set('database.default', 'testing');

		/*
		$migration = include __DIR__.'/../database/migrations/create_laravel-two-factor_table.php.stub';
		$migration->up();
		*/
	}
}
