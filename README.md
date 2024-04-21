# Laravel Permanent Cache

[![Total Downloads](https://img.shields.io/packagist/dt/vormkracht10/laravel-permanent-cache.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/laravel-permanent-cache)
[![Tests](https://github.com/vormkracht10/laravel-permanent-cache/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/vormkracht10/laravel-permanent-cache/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/vormkracht10/laravel-permanent-cache/actions/workflows/phpstan.yml/badge.svg?branch=main)](https://github.com/vormkracht10/laravel-permanent-cache/actions/workflows/phpstan.yml)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/vormkracht10/laravel-permanent-cache)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/vormkracht10/laravel-permanent-cache)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/vormkracht10/laravel-permanent-cache.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/laravel-permanent-cache)

This package aims to provide functionality of using a **permanent** cache for heavy Eloquent models, database queries or other long running tasks in your Laravel app. The permanent cache updates itself in the background using a scheduled task or by listening to an event so no users are harmed waiting on a given request.

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

You can register caches in multiple ways:

```php
use \Vormkracht10\PermanentCache\Facades\PermanentCache;

# When you don't need parameters per class, you can use direct parameters or an array:

# Without array
PermanentCache::caches(
    LongRunningTask::class,
    LongerRunningTask::class,
);

# As an array
$caches = [
    LongRunningTask::class,
    LongerRunningTask::class,
];

PermanentCache::caches($caches);

# Specifying parameters per class
PermanentCache::caches([
    LongRunningTask::class => ['type' => 'long'],
    LongerRunningTask::class => ['type' => 'longer'],
]);

# As an multi-dimensional array when you need to use the same class multiple times but with different parameters
PermanentCache::caches(
    [LongRunningTask::class => ['type' => 'long']],
    [LongRunningTask::class => ['type' => 'longer']],
);
```

## Definition of a Permanent Cache

A Permanent Cache could be a task that runs longer than you'd actually want and not want to bother your users with. That's why you need to run it in the background, updating periodically using the scheduler or when events happen and/or using help of Laravel's queue system.

You can define the cache store and key using a `$store` property on the class, following the definition: `store:key`, for example: `redis:a-unique-cache-key`:

```php
use Vormkracht10\PermanentCache\Cached;

class LongRunningTask extends Cached
{
    protected $store = 'redis:a-unique-cache-key';

    public function run(): string
    {
        return "I'm executing a long running task!";
    }
}
```

## Caches can listen for events

Permanent Caches can be updated by listening to events using an array on the `$events` property:

```php
use Vormkracht10\PermanentCache\Cached;

class LongRunningTaskListeningForEvents extends Cached
{
    protected $store = 'redis:unique-cache-key';

    protected $events = [
        TestEvent::class,
    ];

    public function run(TestEvent $event): string
    {
        return "I'm executing because of {$event->name}!";
    }
}
```

## Caches can be updated periodically using the scheduler

Permanent Caches can be updated using the scheduler (while also listening for events) by adding a `schedule` method or a `$expression` property with a cron syntax:

```php
use Vormkracht10\PermanentCache\Cached;
use Vormkracht10\PermanentCache\Scheduled;

class LongRunningTaskExecutedPeriodicallyOrWhenAnEventHappens extends Cached implements Scheduled
{
    protected $store = 'redis:unique-cache-key';

    protected $events = [
        TestEvent::class,
    ];

    // Use cron expression
    protected $expression = '* * * * *';

    public function run(): string
    {
        return "I'm executing because of {$event->name} or a scheduled run!";
    }

    // Or use the `schedule` method using a callback
    public static function schedule($callback)
    {
        return $callback->everyHour();
    }
}
```

## Caches can be updated by dispatching on the queue

Permanent Caches can be updated using a dispatch to the queue by implementing Laravel's `ShouldQueue` interface and (optionally) specifying a queue:

```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Vormkracht10\PermanentCache\Cached;
use Vormkracht10\PermanentCache\Scheduled;

class LongRunningTaskExecutedPeriodicallyOrWhenAnEventHappensDispatchedOnTheQueue extends Cached implements Scheduled, ShouldQueue
{
    protected $store = 'redis:unique-cache-key';

    protected $events = [
        TestEvent::class,
    ];

    public $queue = 'execute-on-this-queue';

    public function run(TestEvent $event): string
    {
        return "I'm dispatching for execution on the queue because of {$event->name} or a scheduled run!";
    }

    public static function schedule($callback)
    {
        return $callback->everyHour();
    }
}
```

## Bonus: Cached Blade Components

One super handy feature are "Cached Components", these are Blade Components that could contain a longer running task on which you don't want your users to wait for completing. So you execute the Blade component when needed in the background,
using the scheduler, or queue, while optionally listening for events to happen that should cause the permanent cache to update.

```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Vormkracht10\PermanentCache\CachedComponent;
use Vormkracht10\PermanentCache\Scheduled;

class HeavyComponent extends CachedComponent implements Scheduled, ShouldQueue
{
    protected $store = 'redis:unique-cache-key';

    public $queue = 'execute-on-this-queue';

    protected $events = [
        TestEvent::class,
    ];

    public function render()
    {
        return view('components.heavy-component');
    }

    public static function schedule($callback)
    {
        return $callback->everyHour();
    }
}
```

When loading your Blade component, it will always use cache instead of executing a long during task:

```blade
<x-long-during-task />
```

## Manually updating permanent caches

Manually updating a permanent caches is very simple. Just use the static `update` method. This will automatically run or queue the execution of the task:

```php
LongTaskInPermanentCache::update(['parameter' => 'value']);
```

## Events dispatched when updating Permanent Caches

These events get dispatched when executing a Permanent Cache class:

```php
# Before updating the cache
use Vormkracht10\PermanentCache\Events\PermanentCacheUpdating;

# When the cache is updated
use Vormkracht10\PermanentCache\Events\PermanentCacheUpdated;
```

##### [Read more on Jobs & Queues](https://laravel.com/docs/queues)

## Credits

-   [Mark van Eijk](https://github.com/vormkracht10)
-   [David den Haan](https://github.com/daviddenhaan)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see the [License File](LICENSE.md) for more information.
