<?php

use Illuminate\Support\Facades\Blade;

require_once 'tests/Unit/CachedComponent/CachedComponent.php';

beforeEach(function () {
    Cache::driver('file')->clear();
    (fn () => $this->cachers = new \SplObjectStorage)->call(app(\Vormkracht10\PermanentCache\PermanentCache::class));
});

test('test cached component is cached second time', function () {
    $time = microtime(true);

    $firstOutput = Blade::renderComponent(new CachedComponent);

    $time = microtime(true);

    $secondOutput = Blade::renderComponent(new CachedComponent);

    $this->assertEquals($firstOutput, $secondOutput);
});
