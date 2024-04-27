<?php

return [
    'components' => [
        'markers' => [
            'enabled' => env('PERMANENT_CACHE_MARKERS_ENABLED', true),
            'hash' => env('PERMANENT_CACHE_MARKERS_HASH', false),
        ],
    ],
];
