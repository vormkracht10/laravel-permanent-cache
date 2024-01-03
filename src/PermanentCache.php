<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Support\Facades\Event;

class PermanentCache
{
    /**
     * @var array<class-string, array<class-string<Cached>>>
     */
    protected array $cachers = [];

    /**
     * @var array<class-string, array<class-string<Cached>>>
     */
    protected array $static = [];

    /**
     * @param  array<int, class-string<Cached>>  $cachers
     * @return $this
     */
    public function caches(array $cachers): self
    {
        foreach ($cachers as $cacher) {
            $event = $cacher::getListenerEvent();

            if (is_null($event)) {
                $static[] = $cacher;

                continue;
            }

            $resolved[$event][] = $cacher;

            Event::listen($event, $cacher);
        }

        $this->cachers = array_merge($this->cachers, $resolved ?? []);
        $this->static = array_merge($this->static, $static ?? []);

        return $this;
    }

    public function staticCaches(): array
    {
        return $this->static;
    }

    public function configuredCaches(): array
    {
        return $this->cachers;
    }
}
