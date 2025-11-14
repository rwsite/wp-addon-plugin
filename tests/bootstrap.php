<?php

// Load autoloader
require_once __DIR__ . '/../src/Autoloader.php';
\WpAddon\Autoloader::register();

// Define constants for tests
if (!defined('RW_LANG')) {
    define('RW_LANG', 'wp-addon');
}

// Mock WordPress functions if not available
if (!function_exists('get_option')) {
    function get_option($key, $default = '') {
        global $mock_options;
        return $mock_options[$key] ?? $default;
    }
}

if (!function_exists('wp_get_additional_image_sizes')) {
    function wp_get_additional_image_sizes() {
        return [];
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value) {
        return $value;
    }
}
