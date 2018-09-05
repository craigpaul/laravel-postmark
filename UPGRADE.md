# Upgrade Guide

## From v2.2 to v2.3

You may remove the `postmark` specific key from  the `config/services.php` file.
We will get your `POSTMARK_SECRET` directly from your `env` file.

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
