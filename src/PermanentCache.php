<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Vormkracht10\PermanentCache\Events\UpdatingPermanentCacheEvent;

class PermanentCache
{
    protected array $cachers = [];

    public function caches(array $cachers): self
    {
        foreach ($cachers as $cacher) {
            $event = $this->resolveEventType($cacher)
                ?: UpdatingPermanentCacheEvent::class;

            $resolved[$event][] = $cacher;

            Event::listen($event, $cacher);
        }

        $this->cachers = array_merge($this->cachers, $resolved ?? []);

        return $this;
    }

    /**
     * @return class-string|false
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    protected function resolveEventType(string $class): string|false
    {
        if (! method_exists($class, 'run')) {
            throw new \Exception('Every cacher needs a run method.');
        }

        return ((new \ReflectionClass($class))
            ->getMethod('run')
            ->getParameters()[0] ?? null)
            ?->getType()
            ?->getName()
            ?? false;
    }

    /**
     * @return Collection<class-string, array<class-string>>
     */
    public function configuredCaches(): Collection
    {
        return collect($this->cachers);
    }
}
