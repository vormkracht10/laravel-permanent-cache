<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;

/**
 * @method mixed run()
 *
 * @template E
 */
abstract class Cached
{
    use InteractsWithQueue, Queueable;

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
        [$driver, $ident] = self::parseCacheString($this->cache
            ?? throw new \Exception('The $cache property in ['.static::class.'] must be overridden'),
        );

        Cache::driver($driver)->forever($ident,
            $this->run($event),
        );
    }

    /**
     * Get the cached value this cacher provides.
     */
    final public static function get(): mixed
    {
        $cache = (new ReflectionClass(static::class))
            ->getProperty('cache')
            ->getDefaultValue();

        [$driver, $ident] = self::parseCacheString($cache
            ?? throw new \Exception('The $cache property in ['.static::class.'] must be overridden'),
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
     * @return class-string<E>|null
     */
    final public static function getListenerEvent(): ?string
    {
        $reflection = new ReflectionClass(static::class);

        $concrete = $reflection->getProperty('event')->getDefaultValue();

        return $concrete ?? ($reflection
            ->getMethod('run')
            ->getParameters()[0] ?? null)
            ?->getType()
            ?->getName();
    }

    /**
     * @return array{string, string}
     */
    private static function parseCacheString(string $cache): array
    {
        [$driver, $ident] = explode(':', $cache) + [1 => null];

        if (is_null($ident)) {
            [$driver, $ident] = [config('cache.default'), $driver];
        }

        return [$driver, $ident];
    }
}
