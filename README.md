<p align="center"><a href="https://postmarkapp.com" target="_blank"><img src="https://postmarkapp.com/images/logo.svg" alt="Postmark" width="240" height="40"></a>
    
# Laravel Postmark

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![StyleCI][ico-style-ci]][link-style-ci]
[![Total Downloads][ico-downloads]][link-downloads]

> [Postmark](https://postmarkapp.com) is the easiest and most reliable way to be sure your important transactional emails get to your customer's inbox.

## Upgrading

Please see [UPGRADE](UPGRADE.md) for details.

## Installation

You can install the package via composer:

``` bash
$ composer require coconutcraig/laravel-postmark
```

The package will automatically register itself.

You can optionally publish the config file with:

```bash
php artisan vendor:publish --provider="Coconuts\Mail\PostmarkServiceProvider" --tag="config"
```

## Usage

Update your `.env` file by adding your server key and set your mail driver to `postmark`.

```php
MAIL_DRIVER=postmark
POSTMARK_SECRET=YOUR-SERVER-KEY-HERE
```

That's it! The mail system continues to work the exact same way as before and you can switch out Postmark for any of the pre-packaged Laravel mail drivers (smtp, mailgun, log, etc...).

> Remember, when using Postmark the sending address used in your emails must be a [valid Sender Signature](http://support.postmarkapp.com/category/45-category) that you have already configured.

## Postmark Templates

Postmark offers a fantastic templating service for you to utilize instead of maintaining your templates within your Laravel application. If you would like to take advantage of that, this package offers an extension on the base `MailMessage` provided out of the box with Laravel. Within a Laravel notification, you can do the following to start taking advantage of Postmark templates.

```php
public function toMail($notifiable)
{
    return (new \Coconuts\Mail\MailMessage)
        ->identifier(8675309)
        ->include([
            'name' => 'Customer Name',
            'action_url' => 'https://example.com/login',
        ]);
}
```

> You may also utilize an alias instead of the template identifier by using the `->alias()` method.

## Postmark Tags

If you rely on categorizing your outgoing emails using Tags in Postmark, you can simply add a header within your Mailable class's build method.

```php
public function build()
{
    $this->withSwiftMessage(function (\Swift_Message $message) {
        $message->getHeaders()->addTextHeader('tag', 'value');
    });
}
```

## Postmark Metadata

Similar to tags, you can also include [metadata](https://postmarkapp.com/support/article/1125-custom-metadata-faq) by adding a header. Metadata headers should be prefixed with `metadata-` where the string that follows is the metadata key.

```php
public function build()
{
    $this->withSwiftMessage(function (\Swift_Message $message) {
        $message->getHeaders()->addTextHeader('metadata-field', 'value');
        $message->getHeaders()->addTextHeader('metadata-another-field', 'another value');
    });
}
```

In this case, the following object will be sent to Postmark as metadata.

```
{
    "field": "value",
    "another-field", "another value"
}
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email craig.paul@coconutcalendar.com instead of using the issue tracker.

## Credits

- [Craig Paul][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/coconutcraig/laravel-postmark.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/craigpaul/laravel-postmark/master.svg?style=flat-square
[ico-style-ci]: https://styleci.io/repos/80351847/shield?branch=master
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/coconutcraig/laravel-postmark.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/coconutcraig/laravel-postmark.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/coconutcraig/laravel-postmark.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/coconutcraig/laravel-postmark
[link-travis]: https://travis-ci.com/craigpaul/laravel-postmark
[link-style-ci]: https://styleci.io/repos/80351847
[link-scrutinizer]: https://scrutinizer-ci.com/g/coconutcraig/laravel-postmark/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/coconutcraig/laravel-postmark
[link-downloads]: https://packagist.org/packages/coconutcraig/laravel-postmark
[link-author]: https://github.com/coconutcraig
[link-contributors]: ../../contributors
[link-20-tag]: https://github.com/coconutcraig/laravel-postmark/tree/v2.0.0
