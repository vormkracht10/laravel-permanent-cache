<?php

use Illuminate\Support\Facades\Blade;
use Vormkracht10\PermanentCache\Facades\PermanentCache;

require_once 'tests/Unit/CachedComponent/ScheduledCachedComponent.php';

beforeEach(function () {
    Cache::driver('file')->clear();
    (fn () => $this->cachers = new \SplObjectStorage)->call(app(\Vormkracht10\PermanentCache\PermanentCache::class));
});

test('test scheduled cached component gets scheduled', function () {
    PermanentCache::caches([
        ScheduledCachedComponent::class
    ]);

    $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
        ->filter(fn ($schedule) => $schedule->description === 'ScheduledCachedComponent');

    expect($events)->toHaveCount(1);
    expect($events->first()->expression)->toBe('* * * * *');
});
