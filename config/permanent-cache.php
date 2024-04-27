<?php

return [
    'components' => [
        // Add markers around the rendered value of Cached Components
        // This helps to identify the cached value in the rendered HTML
        // Which is useful for debugging and testing, but also for updating
        // the cached value inside another cache when using nested caches
        'markers' => [
            'enabled' => env('PERMANENT_CACHE_MARKERS_ENABLED', true),
            'hash' => env('PERMANENT_CACHE_MARKERS_HASH', false),
        ],
    ],
];