<?php

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Disable Patchwork completely to avoid function redefinition issues
if (class_exists('Patchwork\Utils')) {
    Patchwork\Utils::disable();
}

// Alternative: Create a no-op Patchwork setup
if (!class_exists('Patchwork')) {
    class Patchwork {
        public static function redefine() { }
        public static function restoreAll() { }
    }
}

// Load autoloader (commented out as composer handles PSR-4)
// require_once __DIR__ . '/../src/Autoloader.php';
// \WpAddon\Autoloader::register();

// Set up in-memory database for tests
global $wpdb, $db;
if (!isset($wpdb)) {
    $wpdb = new stdClass();
    $wpdb->prefix = 'wp_';
}

// Create SQLite in-memory database
$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create minimal WordPress schema
$db->exec("
    CREATE TABLE wp_options (
        option_id INTEGER PRIMARY KEY AUTOINCREMENT,
        option_name TEXT NOT NULL,
        option_value LONGTEXT,
        autoload VARCHAR(20) DEFAULT 'yes'
    );
    CREATE UNIQUE INDEX option_name_index ON wp_options (option_name);

    CREATE TABLE wp_posts (
        ID INTEGER PRIMARY KEY AUTOINCREMENT,
        post_author INTEGER DEFAULT 0,
        post_date DATETIME DEFAULT '0000-00-00 00:00:00',
        post_date_gmt DATETIME DEFAULT '0000-00-00 00:00:00',
        post_content LONGTEXT,
        post_title TEXT,
        post_excerpt TEXT,
        post_status VARCHAR(20) DEFAULT 'publish',
        comment_status VARCHAR(20) DEFAULT 'open',
        ping_status VARCHAR(20) DEFAULT 'open',
        post_password VARCHAR(255),
        post_name VARCHAR(200),
        to_ping TEXT,
        pinged TEXT,
        post_modified DATETIME DEFAULT '0000-00-00 00:00:00',
        post_modified_gmt DATETIME DEFAULT '0000-00-00 00:00:00',
        post_content_filtered LONGTEXT,
        post_parent INTEGER DEFAULT 0,
        guid VARCHAR(255),
        menu_order INTEGER DEFAULT 0,
        post_type VARCHAR(20) DEFAULT 'post',
        post_mime_type VARCHAR(100),
        comment_count BIGINT DEFAULT 0
    );
    CREATE INDEX post_name_index ON wp_posts (post_name);
    CREATE INDEX type_status_date_index ON wp_posts (post_type, post_status, post_date, ID);
    CREATE INDEX post_parent_index ON wp_posts (post_parent);
    CREATE INDEX post_author_index ON wp_posts (post_author);

    CREATE TABLE wp_postmeta (
        meta_id INTEGER PRIMARY KEY AUTOINCREMENT,
        post_id INTEGER DEFAULT 0,
        meta_key VARCHAR(255),
        meta_value LONGTEXT
    );
    CREATE INDEX post_id_index ON wp_postmeta (post_id);
    CREATE INDEX meta_key_index ON wp_postmeta (meta_key);

    CREATE TABLE wp_users (
        ID INTEGER PRIMARY KEY AUTOINCREMENT,
        user_login VARCHAR(60) NOT NULL,
        user_pass VARCHAR(255) NOT NULL,
        user_nicename VARCHAR(50) NOT NULL,
        user_email VARCHAR(100) NOT NULL,
        user_url VARCHAR(100),
        user_registered DATETIME DEFAULT '0000-00-00 00:00:00',
        user_activation_key VARCHAR(255),
        user_status INTEGER DEFAULT 0,
        display_name VARCHAR(250)
    );
    CREATE UNIQUE INDEX user_login_key ON wp_users (user_login);
    CREATE INDEX user_nicename_index ON wp_users (user_nicename);
    CREATE INDEX user_email_index ON wp_users (user_email);

    CREATE TABLE wp_usermeta (
        umeta_id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER DEFAULT 0,
        meta_key VARCHAR(255),
        meta_value LONGTEXT
    );
    CREATE INDEX user_id_index ON wp_usermeta (user_id);
    CREATE INDEX meta_key_user_index ON wp_usermeta (meta_key);
");

// Mock $wpdb functions
if (!function_exists('get_option')) {
    function get_option($key, $default = '') {
        global $mock_functions;
        if (isset($mock_functions['get_option']) && is_callable($mock_functions['get_option'])) {
            return $mock_functions['get_option']($key, $default);
        }
        global $db;
        $stmt = $db->prepare("SELECT option_value FROM wp_options WHERE option_name = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['option_value'] : $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($key, $value) {
        global $db;
        $stmt = $db->prepare("INSERT OR REPLACE INTO wp_options (option_name, option_value) VALUES (?, ?)");
        return $stmt->execute([$key, $value]);
    }
}

if (!function_exists('add_option')) {
    function add_option($key, $value, $deprecated = '', $autoload = 'yes') {
        global $db;
        $stmt = $db->prepare("INSERT OR IGNORE INTO wp_options (option_name, option_value, autoload) VALUES (?, ?, ?)");
        return $stmt->execute([$key, $value, $autoload]);
    }
}

// Global mock storage
global $mock_functions;
$mock_functions = [];

if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/');
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

if (!function_exists('add_action')) {
    function add_action($tag, $callback, $priority = 10, $accepted_args = 1) {
        // Mock add_action - do nothing
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
        return \is_dir($filename);
    }
}

if (!function_exists('mkdir')) {
    function mkdir($filename, $mode = 0777, $recursive = false) {
        return \mkdir($filename, $mode, $recursive);
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
        return \file_put_contents($filename, $data);
    }
}

if (!function_exists('gzcompress')) {
    function gzcompress($data, $level = -1) {
        return \gzcompress($data, $level);
    }
}

if (!function_exists('gzuncompress')) {
    function gzuncompress($data) {
        return \gzuncompress($data);
    }
}

if (!function_exists('md5')) {
    function md5($str) {
        return \md5($str);
    }
}

if (!function_exists('home_url')) {
    function home_url($path = '') {
        return 'http://localhost' . $path;
    }
}

if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir() {
        return [
            'path' => '/tmp/uploads',
            'url' => 'http://localhost/wp-content/uploads',
            'subdir' => '',
            'basedir' => '/tmp/uploads',
            'baseurl' => 'http://localhost/wp-content/uploads',
            'error' => false,
        ];
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}
