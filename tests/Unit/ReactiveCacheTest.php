<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Vormkracht10\PermanentCache\Cached;
use Vormkracht10\PermanentCache\Events\PermanentCacheUpdated;
use Vormkracht10\PermanentCache\Events\PermanentCacheUpdating;
use Vormkracht10\PermanentCache\Facades\PermanentCache;

beforeEach(function () {
    Cache::driver('array')->clear();
    (fn () => $this->cachers = new \SplObjectStorage)->call(app(\Vormkracht10\PermanentCache\PermanentCache::class));
});

class TestEvent
{
}

class TestCache extends Cached
{
    protected $store = 'array:test';

    public function run(TestEvent $_): mixed
    {
        return 'it works!';
    }
}

test('caches (listeners) get registered properly when using the PermanentCache facade', function () {
    $events = Event::fake([TestEvent::class]);

    PermanentCache::caches([TestCache::class]);

    $caches = PermanentCache::configuredCaches();

    expect($caches)
        ->count()->toBe(1)
        ->current()->toBeInstanceOf(TestCache::class)
        ->and($events)->hasListeners(TestEvent::class);
});

test('a cache will get updated when an event it\'s listening to gets fired', function () {
    global $pass;
    $pass = false;

    class T extends Cached
    {
        public function run(TestEvent $_)
        {
            global $pass;
            $pass = true;
        }
    }

    Event::fakeExcept(TestEvent::class);
    PermanentCache::caches(T::class);
    event(new TestEvent);

    expect($pass)->toBeTrue();
    unset($pass);
});

test('a cache will dispatch the updating and updated events when it gets invoked', function () {
    Event::fakeExcept(TestEvent::class);
    Permanentcache::caches(TestCache::class);
    event(new TestEvent);
    Event::assertDispatchedTimes(PermanentCacheUpdating::class, times: 1);
    Event::assertDispatchedTimes(PermanentCacheUpdated::class, times : 1);
});
