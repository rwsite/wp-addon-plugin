<?php

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load autoloader (commented out as composer handles PSR-4)
// require_once __DIR__ . '/../src/Autoloader.php';
// \WpAddon\Autoloader::register();

// Global mock storage
global $mock_functions;
$mock_functions = [];

if (!defined('ABSPATH')) {
    define('ABSPATH', '/var/www/no-borders.ru/');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content/');
}

// Mock WordPress functions for AssetMinification testing
if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all') {
        global $wp_styles;
        if (!$wp_styles) {
            $wp_styles = new stdClass();
            $wp_styles->queue = [];
            $wp_styles->registered = [];
        }
        $wp_styles->queue[] = $handle;
        $wp_styles->registered[$handle] = (object) [
            'handle' => $handle,
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'args' => $media,
        ];
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
        global $wp_scripts;
        if (!$wp_scripts) {
            $wp_scripts = new stdClass();
            $wp_scripts->queue = [];
            $wp_scripts->registered = [];
        }
        $wp_scripts->queue[] = $handle;
        $wp_scripts->registered[$handle] = (object) [
            'handle' => $handle,
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'args' => $in_footer,
        ];
    }
}

if (!function_exists('wp_dequeue_style')) {
    function wp_dequeue_style($handle) {
        global $wp_styles;
        if ($wp_styles && isset($wp_styles->queue)) {
            $wp_styles->queue = array_diff($wp_styles->queue, [$handle]);
        }
    }
}

if (!function_exists('wp_dequeue_script')) {
    function wp_dequeue_script($handle) {
        global $wp_scripts;
        if ($wp_scripts && isset($wp_scripts->queue)) {
            $wp_scripts->queue = array_diff($wp_scripts->queue, [$handle]);
        }
    }
}

if (!function_exists('wp_add_inline_script')) {
    function wp_add_inline_script($handle, $data, $position = 'after') {
        global $wp_inline_scripts;
        if (!$wp_inline_scripts) {
            $wp_inline_scripts = [];
        }
        $wp_inline_scripts[$handle] = $data;
    }
}

if (!function_exists('wp_head')) {
    function wp_head() {
        do_action('wp_head');
    }
}

if (!function_exists('wp_footer')) {
    function wp_footer() {
        do_action('wp_footer');
    }
}

if (!function_exists('content_url')) {
    function content_url($path = '') {
        return 'http://localhost/wp-content' . $path;
    }
}

if (!function_exists('site_url')) {
    function site_url($path = '') {
        return 'http://localhost' . $path;
    }
}

if (!function_exists('get_template_directory')) {
    function get_template_directory() {
        global $mock_functions;
        return $mock_functions['get_template_directory'] ?? '/path/to/theme';
    }
}

if (!function_exists('wp_doing_ajax')) {
    function wp_doing_ajax() {
        global $mock_functions;
        return $mock_functions['wp_doing_ajax'] ?? false;
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        global $mock_functions;
        return $mock_functions['is_admin'] ?? false;
    }
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

if (!function_exists('do_action')) {
    function do_action($tag, ...$args) {
        // Mock action - do nothing
    }
}

// Mock file system functions
if (!function_exists('file_exists')) {
    function file_exists($filename) {
        global $mock_functions;
        return $mock_functions['file_exists'] ?? true;
    }
}

if (!function_exists('is_dir')) {
    function is_dir($filename) {
        return true;
    }
}

if (!function_exists('mkdir')) {
    function mkdir($filename, $mode = 0777, $recursive = false) {
        return true;
    }
}

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($dir) {
        return true;
    }
}

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return dirname($file) . '/';
    }
}

if (!function_exists('filesize')) {
    function filesize($filename) {
        global $mock_functions;
        return $mock_functions['filesize'] ?? 2048;
    }
}

if (!function_exists('filemtime')) {
    function filemtime($filename) {
        return time();
    }
}

if (!function_exists('glob')) {
    function glob($pattern, $flags = 0) {
        return [];
    }
}

if (!function_exists('file_get_contents')) {
    function file_get_contents($filename) {
        return 'mock content';
    }
}

if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data) {
        return strlen($data);
    }
}

if (!function_exists('gzcompress')) {
    function gzcompress($data, $level = -1) {
        return $data . '_compressed';
    }
}

if (!function_exists('gzuncompress')) {
    function gzuncompress($data) {
        global $mock_functions;
        return $mock_functions['gzuncompress'] ?? str_replace('_compressed', '', $data);
    }
}

if (!function_exists('md5')) {
    function md5($str) {
        return 'mock_md5_' . $str;
    }
}

if (!function_exists('time')) {
    function time() {
        return 1234567890;
    }
}
