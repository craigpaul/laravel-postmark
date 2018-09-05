<p align="center"><a href="https://postmarkapp.com" target="_blank"><img src="https://postmarkapp.com/images/logo.svg" alt="Postmark" width="240" height="40"></a>
    
# Laravel Postmark

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
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
[ico-travis]: https://img.shields.io/travis/coconutcraig/laravel-postmark/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/coconutcraig/laravel-postmark.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/coconutcraig/laravel-postmark.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/coconutcraig/laravel-postmark.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/coconutcraig/laravel-postmark
[link-travis]: https://travis-ci.org/coconutcraig/laravel-postmark
[link-scrutinizer]: https://scrutinizer-ci.com/g/coconutcraig/laravel-postmark/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/coconutcraig/laravel-postmark
[link-downloads]: https://packagist.org/packages/coconutcraig/laravel-postmark
[link-author]: https://github.com/coconutcraig
[link-contributors]: ../../contributors
[link-20-tag]: https://github.com/coconutcraig/laravel-postmark/tree/v2.0.0
