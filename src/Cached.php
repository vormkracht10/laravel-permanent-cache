<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;

/**
 * @method mixed run()
 *
 * @template V
 */
trait Cached
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
     * @var class-string|array<int, class-string>|null
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
     * @internal You shouldn't call this yourself.
     */
    final public function handle($event = null): void
    {
        [$driver, $ident] = self::store();

        if (is_subclass_of($this, \Vormkracht10\PermanentCache\CachedComponent::class)) {
            $method = 'render';

            /** @phpstan-ignore-next-line */
            if (null === $update = (string) $this->{$method}($event)) {
                return;
            }
        } else {
            $method = 'run';

            /** @phpstan-ignore-next-line */
            if (null === $update = $this->{$method}($event)) {
                return;
            }
        }

        Cache::driver($driver)->forever($ident, $update);
    }

    /**
     * Manually force a static cache to update.
     */
    final public static function update(): ?PendingDispatch
    {
        $instance = app()->make(static::class);

        if (! is_a(static::class, ShouldQueue::class, true)) {
            $instance->handle();

            return null;
        }

        return dispatch($instance);
    }

    /**
     * Get the cached value this cacher provides.
     *
     * @param  bool  $update  Whether the cache should update
     *                        when it doesn't hold the value yet.
     * @return V|mixed|null
     */
    final public static function get($default = null, bool $update = false): mixed
    {
        [$driver, $ident] = self::store();

        $cache = Cache::driver($driver);

        if ($update && ! $cache->has($ident)) {
            static::update()?->onConnection('sync');
        }

        return $cache->get($ident, $default);
    }

    /**
     * Get the cached value this cacher provides.
     *
     * This method should be used inside your cachers
     * instead of the static `static::get` method to prevent
     * infinite recursion.
     *
     * @return V|mixed|null
     */
    final protected function value($default = null): mixed
    {
        [$driver, $ident] = self::store();

        return Cache::driver($driver)->get(
            $ident, $default,
        );
    }

    /// Default implementation for the `\Scheduled::schedule` method.
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
     * @return array<int, class-string>
     */
    final public static function getListenerEvents(): array
    {
        return once(function () {
            $reflection = new ReflectionClass(static::class);

            $concrete = Arr::wrap($reflection->getProperty('event')->getDefaultValue());

            if ($reflection->isSubclassOf(\Vormkracht10\PermanentCache\CachedComponent::class)) {
                $method = 'render';
            } else {
                $method = 'run';
            }

            /** @phpstan-ignore-next-line */
            return $concrete ?: Arr::wrap(($reflection
                ->getMethod($method)
                ->getParameters()[0] ?? null)
                ?->getType()
                ?->getName());
        });
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

    /**
     * Get the driver and identifier specified in the $store property.
     *
     * @return array{string, string}
     */
    private static function store(): array
    {
        return once(function () {
            $store = (new ReflectionClass(static::class))
                ->getProperty('store')
                ->getDefaultValue();

            return self::parseCacheString($store
                ?? throw new \Exception('The $store property in ['.static::class.'] must be overridden'),
            );
        });
    }
}
