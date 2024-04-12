# Laravel Permanent Cache

[![Total Downloads](https://img.shields.io/packagist/dt/vormkracht10/laravel-permanent-cache.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/laravel-permanent-cache)
[![Tests](https://github.com/vormkracht10/laravel-permanent-cache/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/vormkracht10/laravel-permanent-cache/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/vormkracht10/laravel-permanent-cache/actions/workflows/phpstan.yml/badge.svg?branch=main)](https://github.com/vormkracht10/laravel-permanent-cache/actions/workflows/phpstan.yml)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/vormkracht10/laravel-permanent-cache)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/vormkracht10/laravel-permanent-cache)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/vormkracht10/laravel-permanent-cache.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/laravel-permanent-cache)

This package aims to provide functionality of using permanent cache for heavy Eloquent models,
database queries or long duration tasks in Laravel. The permanent cache updates itself
in the background using a scheduled task or by reacting to an event
so no visitors are harmed waiting long on a given request.

## Installation

You can install the package via composer:

```bash
composer require vormkracht10/laravel-permanent-cache
```

# Usage

This package provides a handy `Cached` class. This allows you to easily 
cache data based on a schedule or an event.

All caches you create must be registered to the `PermanentCache::caches` facade. 
We recommend putting this in the `boot` method of your `AppServiceProvider`.

```php
use \Vormkracht10\PermanentCache\Facades\PermanentCache;

PermanentCache::caches([
    \App\Cache\SomeCache::class,
]);
```

## "Reactive" caches

To get started with this Cached class, make a `HelloCache` class like so.
This cache will respond to a `TestEvent` by caching whatever will
be returned from the `run` method.

```php
use Vormkracht10\PermanentCache\Cached;

// ...

class HelloCache extends Cached
{
    protected $store = 'redis:hello';

    public function run(TestEvent $event): string
    {
        return "Hallo, {$event->name}!";
    }
}
```

##### if you don't want to type hint the `TestEvent` class in the `run` method, you can also explicitly specify the type like so `protected $event = TestEvent::class;`

To know *where* to cache the returned value, we have the `$store` property.
This is formatted like `driver:identifier`, but you can also omit the `driver:` 
like so `protected $store = 'hello';` and we will use the config's `cache.default` value instead.

---

To get the value from a cache class, you can use the static `get` method.

```php
$greeting = HelloCache::get();
```

---

You can specify a default value too.

```php
$greeting = HelloCache::get('Welcome');
```

---

If you want the cache to update when it doesn't hold a value for
your cache yet, you can set the `$update` argument to true.

```php
$greeting = HelloCache::get(update: true);
```

---

### Reactive caches that listen to many events

Sometimes you may want to update a cache when one of many events occur,
for example you might want to gather some information on a page when
some content is created, deleted or updated. 

You can do that by assigning the `$event` property an array of event names.

```php
class SomeCache extends Cached 
{
    protected $event = [
        ContentCreated::class,
        ContentDeleted::class,
        ContentUpdated::class,
    ];

    // You have access to an $event object here,
    // but be careful! This event object may be any of
    // the events listed above.
    public function run($event)
    {
        // do something...
    }
}
```

## "Static" caches

Static caches are a little different to the Reactive caches, these do not respond to events
and must be updated manually or scheduled. Here is an example.

### Scheduling with cron expressions

You can use cron expressions to schedule your cache, a very basic example is shown below.
This will run the cache every minute. 

```php
use Vormkracht10\PermanentCache\Scheduled;

// ...

class MinutesCache extends Cached implements Scheduled
{
    protected $store = 'redis:minutes';

    protected $expression = '* * * * *';

    public function run(): int
    {
        return $this->value() + 1;
    }
}
```

##### Warning: you should not use Cache::get() in the cache itself, use $this->value() instead, this is to prevent infinite recursion from happening.

### Static caches with Laravel magic

Now you can run `php artisan schedule:work` and every minute, the `minutes` count will be incremented.
But, if you're anything like me, you don't really like writing raw cron expressions
and much rather use Laravel's cool `Schedule` class. Well, you can.

Let's take our previous snippet, and edit it a little to use Laravel's `Schedule` instead.

```php
class MinutesCache extends Cached implements Scheduled
{
    protected $store = 'redis:minutes';

    public function run(): mixed
    {
        return $this->value() + 1;
    }
    
    public static function schedule($callback)
    {
        $callback->everyMinute();
    }
}
```

### Manually updating static caches.

Manually updating static caches is very simple. Just use the static `update` method.
This will automatically queue your job, if it should be.

```php
MinutesCache::update();
```

## Queued caches

You can also queue caches, for both the static and reactive caches.
You can do this by simply implementing Laravel's `ShouldQueue` interface!

```php
class HelloCache extends Cached implements ShouldQueue
{
    protected $connection = 'redis';

    protected $store = 'redis:hello';

    public function run(TestEvent $event): string
    {
        return "Hallo, {$event->name}!";
    }
}
```

You can specify a bunch of things, like the queue connection using the `$connection` property.
You can basically configure you cache as a Laravel job. This works because the `Cached` class from which 
we are inheriting is structured like a Laravel job.

##### [Read more on Jobs & Queues](https://laravel.com/docs/queues)

## Credits

-   [Mark van Eijk](https://github.com/vormkracht10)
-   [David den Haan](https://github.com/daviddenhaan)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see the [License File](LICENSE.md) for more information.
