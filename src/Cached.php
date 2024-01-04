<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;

/**
 * @method mixed run()
 *
 * @template E
 */
abstract class Cached
{
    use Queueable;

    /**
     * The driver and identifier that will be used to cache this value.
     * This value should be in the format 'driver:identifier'.
     *
     * @var string|null
     */
    protected $store = null;

    /**
     * The event that this cacher will listen for, this is optional
     * as it can also be inferred by type hinting an argument
     * in the run method.
     *
     * @var class-string<E>|null
     */
    protected $event = null;

    /**
     * The cron expression that will be used if the `schedule`
     * method has not been overridden.
     *
     * @var string|null
     */
    protected $expression = null;

    /**
     * Update the cached value, this method expects an event if
     * the cacher is not static.
     *
     * @param  E  $event
     */
    final public function handle($event = null): void
    {
        [$driver, $ident] = self::parseCacheString($this->store
            ?? throw new \Exception('The $store property in ['.static::class.'] must be overridden'),
        );

        Cache::driver($driver)->forever($ident,
            /** @phpstan-ignore-next-line */
            $this->run($event),
        );
    }

    /**
     * Manually force a static cache to update.
     */
    final public static function update(): void
    {
        $instance = app()->make(static::class);

        if (! is_a(static::class, ShouldQueue::class, true)) {
            $instance->handle();

            return;
        }

        dispatch($instance);
    }

    /**
     * Get the cached value this cacher provides.
     */
    final public static function get(): mixed
    {
        $store = (new ReflectionClass(static::class))
            ->getProperty('store')
            ->getDefaultValue();

        [$driver, $ident] = self::parseCacheString($store
            ?? throw new \Exception('The $store property in ['.static::class.'] must be overridden'),
        );

        return Cache::driver($driver)->get($ident);
    }

    // Default implementation for the \Scheduled::schedule method.
    public static function schedule($callback)
    {
        if (! is_a(static::class, Scheduled::class, true)) {
            throw new \Exception('Can not schedule a cacher that does not implement the ['.Scheduled::class.'] interface');
        }

        $reflection = new ReflectionClass(static::class);

        $concrete = $reflection->getProperty('expression')->getDefaultValue();

        if (is_null($concrete)) {
            throw new \Exception('Either the Cached::$expression property or the ['.__METHOD__.'] method must be overridden by the user.');
        }

        $callback->cron($concrete);
    }

    /**
     * Get the event (if any) this cacher listens for.
     *
     * @return array<int, class-string<E>>
     */
    final public static function getListenerEvent(): array
    {
        $reflection = new ReflectionClass(static::class);

        $concrete = Arr::wrap($reflection->getProperty('event')->getDefaultValue());

        /** @phpstan-ignore-next-line */
        return $concrete ?: Arr::wrap(($reflection
            ->getMethod('run')
            ->getParameters()[0] ?? null)
            ?->getType()
            ?->getName());
    }

    /**
     * @return array{string, string}
     */
    private static function parseCacheString(string $store): array
    {
        [$driver, $ident] = explode(':', $store) + [1 => null];

        if (is_null($ident)) {
            [$driver, $ident] = [config('cache.default'), $driver];
        }

        return [$driver, $ident];
    }
}
