# Laravel Permanent Cache

[![Total Downloads](https://img.shields.io/packagist/dt/vormkracht10/laravel-permanent-cache.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/laravel-permanent-cache)
[![Tests](https://github.com/vormkracht10/laravel-permanent-cache/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/vormkracht10/laravel-permanent-cache/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/vormkracht10/laravel-permanent-cache/actions/workflows/phpstan.yml/badge.svg?branch=main)](https://github.com/vormkracht10/laravel-permanent-cache/actions/workflows/phpstan.yml)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/vormkracht10/laravel-permanent-cache)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/vormkracht10/laravel-permanent-cache)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/vormkracht10/laravel-permanent-cache.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/laravel-permanent-cache)

This package aims to provide functionality of using permanent cache for heavy Eloquent models, database queries or long duration tasks in Laravel. The permanent cache updates itself in the background using a scheduled task, so no visitors are harmed waiting long on a given request.

## Installation

You can install the package via composer:

```bash
composer require vormkracht10/laravel-permanent-cache
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-permanent-cache-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$permanentCache = new Vormkracht10\PermanentCache();
echo $permanentCache->echoPhrase('Hello, Vormkracht10!');
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

-   [Mark van Eijk](https://github.com/vormkracht10)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
