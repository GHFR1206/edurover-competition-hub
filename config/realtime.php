<?php

/**
 * Browser-facing realtime settings. These differ from the server-side
 * broadcasting connection (which uses the docker-internal host/port).
 */
return [
    'key' => env('REVERB_APP_KEY'),
    'host' => env('VITE_REVERB_HOST', 'localhost'),
    'port' => env('VITE_REVERB_PORT', 8090),
    'scheme' => env('VITE_REVERB_SCHEME', 'http'),
];
