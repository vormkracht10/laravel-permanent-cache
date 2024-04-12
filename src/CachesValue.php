<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use ReflectionClass;

/**
 * @template V
 */
trait CachesValue
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

        /** @phpstan-ignore-next-line */
        if (null === $value = $this->{self::getUpdateMethodString()}($event)) {
            return;
        }

        if (is_a($value, View::class)) {
            $value = (string) $value;
        }

        Cache::driver($driver)->forever($ident, $value);
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
        $class = static::class;

        return once(function () use ($class) {
            $reflection = new ReflectionClass($class);

            $concrete = Arr::wrap($reflection->getProperty('event')->getDefaultValue());

            /** @phpstan-ignore-next-line */
            return $concrete ?: Arr::wrap(($reflection
                ->getMethod(self::getUpdateMethodString())
                ->getParameters()[0] ?? null)
                ?->getType()
                ?->getName());
        });
    }

    private static function getUpdateMethodString(): string
    {
        return is_subclass_of(static::class, CachedComponent::class) ? 'render' : 'run';
    }

    /**
     * @return array{string, string}
     */
    private static function parseCacheString($class, ?string $store): array
    {
        if ($store && strpos($store, ':')) {
            $cacheDriver = substr($store, 0, strpos($store, ':'));
            $cacheKey = substr($store, strpos($store, ':') + 1);
        } else {
            $cacheDriver = $store;
        }

        $cacheDriver ??= config('cache.default');
        $cacheKey ??= str_replace('\\', '_', strtolower($class));

        $cacheKey = preg_replace('/[^A-Za-z0-9]+/', '_', $cacheKey);

        return [$cacheDriver, $cacheKey];
    }

    /**
     * Get the driver and identifier specified in the $store property.
     *
     * @return array{string, string}
     */
    private static function store(): array
    {
        $class = static::class;

        $store = (new ReflectionClass($class))
            ->getProperty('store')
            ->getDefaultValue();

        return self::parseCacheString($class, $store);
    }
}
