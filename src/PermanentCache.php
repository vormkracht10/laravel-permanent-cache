<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use SplObjectStorage;

class PermanentCache
{
    protected SplObjectStorage $cachers;

    public function __construct(protected Application $app)
    {
        $this->cachers = new SplObjectStorage;
    }

    /**
     * @param  array<int, class-string<Cached|CachedComponent>>  $cachers
     */
    public function caches(array $cachers): self
    {
        foreach ($cachers as $cacher => $parameters) {
            if (is_int($cacher)) {
                $cacher = $parameters;
                $parameters = [];
            }

            $cacher = $this->app->make($cacher, $parameters);

            $events = $cacher::getListenerEvents();

            Event::listen($events, $cacher);

            $this->cachers[$cacher] = $events;
        }

        return $this;
    }

    public function configuredCaches(): SplObjectStorage
    {
        return $this->cachers;
    }
}
