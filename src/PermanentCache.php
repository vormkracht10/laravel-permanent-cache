<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use SplObjectStorage;

class PermanentCache
{
    public function __construct(
        protected SplObjectStorage $cachers,
        protected Application $app,
    ) {
        //
    }

    /**
     * @param  array<class-string<Cached|CachedComponent>, int>  $cachers
     */
    public function caches($registeredCaches): self
    {
        $registeredCaches = func_get_args();

        if (! is_array(array_key_first($registeredCaches))) {
            $registeredCaches = [$registeredCaches];
        }

        foreach ($registeredCaches as $registeredCache) {
            foreach ($registeredCache as $cacher => $parameters) {
                if (is_int($cacher)) {
                    if (is_string($parameters)) {
                        $cacher = $parameters;
                        $parameters = [];
                    } elseif (is_string(array_key_first($parameters))) {
                        $cacher = array_key_first($parameters);
                        $parameters = array_shift($parameters);
                    } else {
                        $cacher = \Arr::first($parameters);
                        $parameters = [];
                    }
                }

                $cacherInstance = $this->app->make($cacher, $parameters);

                if ([] !== $events = $cacherInstance->getListenerEvents()) {
                    Event::listen($events, fn ($event) => $cacherInstance->handle($event));
                }

                $this->cachers[$cacherInstance] = $events;
            }
        }

        return $this;
    }

    public function configuredCaches(): SplObjectStorage
    {
        return $this->cachers;
    }
}
