# Upgrade Guide

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

## From v2.0 to v2.1

When upgrading from v2.0 to v2.1, you can remove the service provider from the providers array in `config/app.php`. This is possible because laravel-postmark v2.1 takes advantage of [Package Auto Discovery](https://laravel-news.com/package-auto-discovery).
