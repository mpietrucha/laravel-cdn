<?php

return [
    'enabled' => env('CDN_ENABLED', true),

    'subdomain' => env('CDN_SUBDOMAIN', 'cdn'),

    'image' => env('CDN_IMAGE_DRIVER', 'imagick'),

    'cache' => [
        'enabled' => env('CDN_CACHE_ENABLED', true),
        'levels' => env('CDN_CACHE_LEVELS', 2),
        'disk' => [
            'driver' => env('CDN_CACHE_DISK_DRIVER', 'local'),
            'root' => env('CDN_CACHE_DISK_ROOT', storage_path('app/cdn/cache')),
        ],
    ],

    'sync' => [
        'enabled' => env('CDN_SYNC_ENABLED', true),
        'path' => env('CDN_SYNC_PATH', 'cloud'),
        'disk' => [
            'driver' => env('CDN_SYNC_DISK_DRIVER', 'local'),
            'root' => env('CDN_SYNC_DISK_ROOT', storage_path('app/cdn/sync')),
        ],
    ],

    'defaults' => [
        'image/*' => [
            'format' => 'webp',
        ],
        'text/*' => [
            'minify' => [],
        ],
    ],

    'disks' => [
        'public',
    ],

    'public' => env('CDN_PUBLIC', true)
];
