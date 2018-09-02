# APM for laravel

[![Latest Version on Packagist][ico-version]][https://packagist.org/packages/oved/apm]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][https://travis-ci.org/OveD-php/apm.svg?branch=master]

This package is a APM (Application Performance Management) package for laravel. It can save requests and database queries made in each request, to help you deploy performance issues.

## Install

Via Composer

``` bash
$ composer require oved/apm
```

## Usage

1. Add `OveD\Apm\ServiceProvider\ApmServiceProvider::class` to app.php
2. Run `php artisan migrate`
3. RUn `php artisan vendor:publish`

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email hr.kloft (at) gmail.com instead of using the issue tracker.

## Credits

- [Visti Kløft][https://twitter.com/vistikloft]
- [Peter Suhm][https://twitter.com/petersuhm]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.