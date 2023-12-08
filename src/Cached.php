<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Support\Facades\Cache;
use ReflectionClass;
use ReflectionException;
use Vormkracht10\PermanentCache\Events\UpdatingPermanentCacheEvent;

/**
 * @method mixed run()
 *
 * @template T
 */
abstract class Cached
{
    /**
     * The driver and identifier that will be used to cache this value.
     * This value should be in the format 'driver:identifier'.
     *
     * @var string|null
     */
    protected $cache = null;

    /**
     * The event that this cacher will listen for, this is optional
     * as it can also be inferred by type hinting an argument
     * in the run method.
     *
     * @var class-string<T>|null
     */
    protected $event = null;

    /**
     * @param T $event
     * @return void
     */
    public final function handle($event = null): void
    {
        [$driver, $ident] = self::parseCacheString($this->cache);

        Cache::driver($driver)->forever($ident,
            $this->run($event),
        );
    }

    public final static function get()
    {
        $cache = (new ReflectionClass(static::class))
            ->getProperty('cache')
            ->getDefaultValue();

        [$driver, $ident] = self::parseCacheString($cache);

        return Cache::driver($driver)->get($ident);
    }

    /**
     * @return class-string<T>
     *
     * @throws ReflectionException
     */
    public final static function getListenerEvent(): string
    {
        $reflection = new ReflectionClass(static::class);

        $concrete = $reflection->getProperty('event')->getDefaultValue();

        return $concrete ?? ($reflection
            ->getMethod('run')
            ->getParameters()
            [0] ?? null)
            ?->getType()
            ?->getName()
            ?? UpdatingPermanentCacheEvent::class;
    }

    /**
     * @param string $cache
     *
     * @return array{string, string}
     */
    private static function parseCacheString(string $cache): array
    {
        [$driver, $ident] = explode(':', $cache) + [1 => null];

        if (is_null($ident)) {
            [$driver, $ident] = [config('permanent-cache.default_store'), $driver];
        }

        return [$driver, $ident];
    }
}
