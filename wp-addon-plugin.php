<?php
/**
 * Plugin Name:  # WordPress Addon
 * Plugin URL:   https://rwsite.ru
 * Description:  Addon for WordPress;
 * Version:      1.3.0
 * Text Domain:  wp-addon
 * Domain Path: /languages/
 * Author:       Aleksey Tikhomirov
 * Author URI:   https://rwsite.ru
 *
 * Tags: wordpress, wp-addon,
 *
 * Requires at least: 5.3
 * Tested up to: 6.8.3
 * Requires PHP: 7.4+
 */

defined('ABSPATH') || exit;

// Register autoloader
require_once __DIR__ . '/src/Autoloader.php';
\WpAddon\Autoloader::register();

// Initialize plugin
$plugin = new \WpAddon\Core\Plugin(__FILE__);
$plugin->init();