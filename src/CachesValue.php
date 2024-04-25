<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Bus\Queueable;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;
use Vormkracht10\PermanentCache\Events\PermanentCacheUpdated;
use Vormkracht10\PermanentCache\Events\PermanentCacheUpdating;

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
     * The events that this cacher will listen for, this is optional
     * as it can also be inferred by type hinting an argument
     * in the run method.
     *
     * @var class-string|array<int, class-string>|null
     */
    protected $events = [];

    /**
     * The cron expression that will be used if the `schedule`
     * method has not been overridden.
     *
     * @var string|null
     */
    protected $expression = null;

    /**
     * The parameters this cache should be stored with.
     *
     * @var array<string, mixed>
     */
    protected $parameters = [];

    /**
     * Indicates whether this cache is currently updating or not.
     *
     * @var bool
     */
    private $isUpdating = false;

    /**
     * Update the cached value, this method expects an event if
     * the cacher is not static.
     *
     * @internal You shouldn't call this yourself, use the `CachesValue::update` method instead.
     */
    final public function handle($event = null): void
    {
        $this->isUpdating = true;

        [$driver, $cacheKey] = $this->store($this->getParameters());

        PermanentCacheUpdating::dispatch($this);

        $value = is_subclass_of(static::class, CachedComponent::class)
            ? Blade::renderComponent($this)
            : $this->run($event);

        if (is_null($value)) {
            return;
        }

        Cache::driver($driver)->forever($cacheKey, $value);

        PermanentCacheUpdated::dispatch($this);

        $this->isUpdating = false;
    }

    public function getParameters()
    {
        return collect((new \ReflectionClass(static::class))
            ->getProperties(\ReflectionProperty::IS_PUBLIC))
            ->filter(fn (\ReflectionProperty $p) => $p->class === static::class)
            ->mapWithKeys(fn (\ReflectionProperty $p) => [$p->name => $p->getValue($this)])
            ->toArray();
    }

    /**
     * Get the driver and identifier specified in the $store property.
     *
     * @return array{string, string}
     */
    private static function store($parameters): array
    {
        $class = static::class;

        $store = (new ReflectionClass($class))
            ->getProperty('store')
            ->getDefaultValue();

        return self::parseCacheString($class, $store, $parameters);
    }

    public function shouldBeUpdating(): bool
    {
        return app()->runningInConsole();
    }

    /**
     * Manually force a static cache to update.
     */
    final public static function update($parameters = []): ?PendingDispatch
    {
        $instance = app()->make(static::class, $parameters);

        if (! is_subclass_of(static::class, ShouldQueue::class)) {
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
        if (is_subclass_of(static::class, CachedComponent::class)) {
            $parameters = $default;
            $default = null;
        }

        [$driver, $cacheKey] = self::store($parameters ?? []);

        $cache = Cache::driver($driver);

        if ($update && ! $cache->has($cacheKey)) {
            static::update($parameters ?? [])->onConnection('sync');
        }

        return $cache->get($cacheKey, $default);
    }

    /**
     * Force an update of the cache and return the updated value.
     *
     * @return V|mixed
     */
    final public static function updateAndGet($parameters = []): mixed
    {
        [$driver, $cacheKey] = self::store($parameters);

        static::update($parameters)->onConnection('sync');

        return Cache::driver($driver)->get($cacheKey);
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
        if (is_subclass_of(static::class, CachedComponent::class) && ! is_null($default)) {
            throw new \Exception("A cached component can't have a default return value");
        }

        [$driver, $cacheKey] = $this->store($this->getParameters());

        return Cache::driver($driver)->get(
            $cacheKey, $default,
        );
    }

    /// Default implementation for the `\Scheduled::schedule` method.
    /** @param CallbackEvent $callback */
    public static function schedule($callback)
    {
        if (! is_a(static::class, Scheduled::class, true)) {
            throw new \Exception("Can't schedule a cacher that does not implement the [".Scheduled::class.'] interface');
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

        $reflection = new ReflectionClass($class);

        $concrete = Arr::wrap($reflection->getProperty('events')->getDefaultValue());

        /** @phpstan-ignore-next-line */
        return $concrete ?: Arr::wrap(($reflection
            ->getMethod(self::getUpdateMethodString())
            ->getParameters()[0] ?? null)
            ?->getType()
            ?->getName());
    }

    private static function getUpdateMethodString(): string
    {
        return is_a(static::class, CachedComponent::class, true) ? 'render' : 'run';
    }

    /**
     * @return array{string, string}
     */
    private static function parseCacheString($class, ?string $store, ?array $parameters = []): array
    {
        if ($store && strpos($store, ':')) {
            $cacheDriver = substr($store, 0, strpos($store, ':'));
            $cacheKey = substr($store, strpos($store, ':') + 1);
        } else {
            $cacheKey = $store;
        }

        $cacheDriver ??= config('cache.default');
        $cacheKey ??= preg_replace('/[^A-Za-z0-9]+/', '_', strtolower(\Str::snake($class)));

        if ($parameters) {
            $cacheKey .= ':'.http_build_query($parameters);
        }

        return [$cacheDriver, $cacheKey];
    }
}
