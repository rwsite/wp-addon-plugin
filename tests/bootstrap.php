<?php

// Create mock Patchwork class BEFORE loading autoloader to prevent conflicts
if (!class_exists('Patchwork')) {
    class Patchwork {
        public static function redefine() { }
        public static function restoreAll() { }
        public static function disable() { }
    }
}

// Mock Patchwork functions
if (!function_exists('redefine')) {
    function redefine() { }
}
if (!function_exists('restoreAll')) {
    function restoreAll() { }
}

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Completely disable any remaining Patchwork functionality
if (class_exists('Patchwork')) {
    if (method_exists('Patchwork', 'disable')) {
        Patchwork::disable();
    }
}

// Remove Patchwork from global functions if it exists
if (isset($GLOBALS['patchwork'])) {
    unset($GLOBALS['patchwork']);
}

// Check if WordPress test environment is available and load it
$wp_tests_dir = getenv('WP_TESTS_DIR');
if ($wp_tests_dir && file_exists($wp_tests_dir . '/includes/bootstrap.php')) {
    // Load WordPress test bootstrap for proper environment
    require_once $wp_tests_dir . '/includes/bootstrap.php';
} else {
    // Fallback for local development without WordPress test suite

    // Set up in-memory database for tests
    global $wpdb, $db;
    if (!isset($wpdb)) {
        $wpdb = new stdClass();
        $wpdb->prefix = 'wp_';
    }

    // Create SQLite in-memory database
    $db = new PDO('sqlite::memory:');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create full WordPress schema for proper testing
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

    // Mock WordPress functions for local testing
    if (!function_exists('get_option')) {
        function get_option($key, $default = '') {
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

    if (!function_exists('wp_die')) {
        function wp_die($message = '', $title = '', $args = []) {
            throw new Exception($message ?: 'WordPress died');
        }
    }

    if (!function_exists('apply_filters')) {
        function apply_filters($tag, $value) {
            return $value;
        }
    }

    if (!function_exists('add_action')) {
        function add_action($tag, $callback, $priority = 10, $accepted_args = 1) {
            // Mock add_action - do nothing
        }
    }

    if (!function_exists('is_admin')) {
        function is_admin() {
            return false;
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

    if (!function_exists('get_file_data')) {
        function get_file_data($file, $headers) {
            return ['Version' => '1.3.4'];
        }
    }

    if (!function_exists('wp_json_encode')) {
        function wp_json_encode($data, $options = 0, $depth = 512) {
            return \json_encode($data, $options, $depth);
        }
    }

    if (!function_exists('__')) {
        function __($text, $domain = 'default') {
            return $text;
        }
    }
}
