# Laravel Two Factor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/buckhamduffy/laravel-two-factor.svg?style=flat-square)](https://packagist.org/packages/buckhamduffy/laravel-two-factor)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/buckhamduffy/laravel-two-factor/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/buckhamduffy/laravel-two-factor/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/buckhamduffy/laravel-two-factor/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/buckhamduffy/laravel-two-factor/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/buckhamduffy/laravel-two-factor.svg?style=flat-square)](https://packagist.org/packages/buckhamduffy/laravel-two-factor)

An opinionated two factor authentication package for Laravel.

## Installation

You can install the package via composer:

```bash
composer require buckhamduffy/laravel-two-factor
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="two-factor-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="two-factor-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="two-factor-views"
```

Add the trait and interface to the user model
```php
    use BuckhamDuffy\LaravelTwoFactor\Traits\HasTwoFactor;
    use BuckhamDuffy\LaravelTwoFactor\Interfaces\HasTwoFactorInterface;

    class User extends Model implements HasTwoFactorInterface {
        use HasTwoFactor;
    }
```

Add the middleware to your Kernel.php
```php
    protected $middlewareAliases = [
        // ...
        '2fa' => \BuckhamDuffy\LaravelTwoFactor\Http\Middleware\TwoFactorMiddleware::class,
    ];
```

```php
    Route::middleware('2fa')->group(function(){
        // Your routes here
    });
```

#### SMS (Not Implemented Yet)

When a code is requested via SMS, an event will be dispatched that you can listen for to send the SMS. You can listen for the `TwoFactorCodeRequested` event and send the SMS using your preferred SMS provider.

```php
    use \BuckhamDuffy\LaravelTwoFactor\Events\TwoFactorCodeRequested;
    
    class EventProvider extends ServiceProvider {
        protected $listen = [
            // ...
            TwoFactorCodeRequested::class => [
                \App\Listeners\SendTwoFactorCode::class,
            ],
        ];
    }
```

```php
    namespace App\Listeners;

    use BuckhamDuffy\LaravelTwoFactor\Events\TwoFactorCodeRequested;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Queue\InteractsWithQueue;

    class SendTwoFactorCode implements ShouldQueue
    {
        use InteractsWithQueue;

        public function handle(TwoFactorCodeRequested $event): void
        {
           $user = $event->getUser();
           
           $user->sendTwoFactorSms($event->getCode());
        }
    }
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Aaron Florey](https://github.com/aaronflorey)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
