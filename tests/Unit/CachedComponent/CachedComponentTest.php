<?php

use Illuminate\Support\Facades\Blade;

require_once 'tests/Unit/CachedComponent/CachedComponent.php';

beforeEach(function () {
    Cache::driver('file')->clear();
    (fn () => $this->caches = new \SplObjectStorage)->call(app(\Vormkracht10\PermanentCache\PermanentCache::class));
});

test('test cached component is cached second time', function () {
    $firstRunOutput = Blade::renderComponent(new CachedComponent);
    $secondRunOutput = Blade::renderComponent(new CachedComponent);

    $this->assertEquals($firstRunOutput, $secondRunOutput);
});
