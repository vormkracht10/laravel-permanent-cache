<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Vormkracht10\PermanentCache\CachesValue;

Route::get('/permanent-cache/{class}/update', function (Request $request, string $class) {
    /** @var class-string<CachesValue> $class */
    $class = decrypt($class, false);

    $parameters = ($parameters = $request->query('parameters'))
        ? Arr::wrap(decrypt($parameters))
        : [];

    if (
        ! class_exists($class) ||
        ! in_array(CachesValue::class, class_uses_recursive($class))
    ) {
        return response()->json([
            'error' => 'the given class does not exist or does not use the ['.CachesValue::class.'] trait',
        ], 400);
    }

    $data = $class::updateAndGet($parameters ?? []);

    return response()->json(compact('data'));
})->name('permanent-cache.update');
