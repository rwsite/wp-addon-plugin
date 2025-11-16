<?php

return [
    'enabled' => true,
    'cache_dir' => WP_CONTENT_DIR . '/cache/pages/',
    'ttl' => 3600, // 1 hour
    'exclude_logged_in' => true,
    'exclude_urls' => [
        '/wp-admin/',
        '/wp-login.php',
        '/checkout/',
        '/cart/',
    ],
    'preload_pages' => [
        '/',
        '/about/',
        '/contact/',
    ],
];
