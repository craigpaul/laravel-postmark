# Upgrade Guide

## From v2.11 to v3.0

With the release of Laravel 9 came an upgrade to Symfony 6, which included adoption of their new `symfony/mailer` component. That meant a complete re-write of this package, which means there are a few things you'll want to check out during the upgrade process.

You may remove the `postmark.php` configuration file that is published by this package. We have adopted Laravel's default configuration for the Postmark configuration within the `services.php` file. If you're still using the `POSTMARK_SECRET` environment variable, be sure to update it to `POSTMARK_TOKEN`.

With the re-write of this package came a change to bring the various classes exposed by this package to follow more of a convention then before. If you are using any of the following classes, you will need to update the usage(s) to match the new class name and namespace.

```php
use Coconuts\Mail\MailMessage; // Before
use CraigPaul\Mail\TemplatedMailMessage; // After
```

```php
use Coconuts\Mail\PostmarkException; // Before
use CraigPaul\Mail\PostmarkTransportException; // After
```

```php
use Coconuts\Mail\PostmarkTemplateMailable; // Before
use CraigPaul\Mail\TemplatedMailable; // After
```

Lastly, be sure to pay special attention to the `symfony/mailer` section of the official [Laravel 9 upgrade guide](https://laravel.com/docs/master/upgrade#symfony-mailer) for any other related changes.

## From v2.10 to v2.11

If you were previously catching either `\GuzzleHttp\Exception\ConnectException` or `GuzzleHttp\Exception\ServerException`, they will now be rethrown as `\Swift_TransportException` with the appropriate message, code and previous exception. This change was made to support Laravel's mail [failover configuration](https://laravel.com/docs/8.x/mail#failover-configuration).

## From v2.2 to v2.3

You may remove the `postmark` specific key from the `config/services.php` file as we will get your `POSTMARK_SECRET` directly from your `env` file.

```php
return [

    // You can remove this:    
    'postmark' => [
        'secret' => env('POSTMARK_SECRET'),    
    ],

];
```

You can optionally publish the config file with:

```bash
php artisan vendor:publish --provider="Coconuts\Mail\PostmarkServiceProvider" --tag="config"
```

## From v2.0 to v2.1

When upgrading from v2.0 to v2.1, you can remove the service provider from the providers array in `config/app.php`. This is possible because laravel-postmark v2.1 takes advantage of [Package Auto Discovery](https://laravel-news.com/package-auto-discovery).

## From v1 to v2.0

When upgrading from v1 to v2.0, you will need to change the old service provider back to the MailServiceProvider that comes with Laravel in `config/app.php`.

``` php
Coconuts\Mail\PostmarkServiceProvider::class,
```

And **replace** it with:

```php
Iluminate\Mail\MailServiceProvider::class,
```

After that, you can add the new service provider to the providers array in `config/app.php`.

```php
Coconuts\Mail\PostmarkServiceProvider::class,
```
