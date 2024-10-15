<?php
/*
 * Plugin Name:  # WordPress Addon
 * Plugin URL:   https://rwsite.ru
 * Description:  Massive addon for WordPress;
 * Version:      1.2.1
 * Text Domain:  wp-addon
 * Domain Path:  /languages
 * Author:       Aleksey Tikhomirov
 * Author URI:   https://rwsite.ru
 *
 * Tags: wordpress, wp-addon,
 *
 * Requires at least: 4.6
 * Tested up to: 6.5.0
 * Requires PHP: 7.4+
 */


defined( 'ABSPATH' ) || exit;


if( !defined('RW_LANG') ) {
    define( 'RW_LANG', 'wp-addon' );
}

if ( ! defined( 'RW_PLUGIN_DIR' ) ) {
    define( 'RW_PLUGIN_DIR', plugin_dir_path(__FILE__ ) );
}

if ( ! defined( 'RW_PLUGIN_URL' ) ) {
    define( 'RW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'RW_FILE' ) ) {
    define( 'RW_FILE', __FILE__);
}


require_once 'wp-addon-settings.php';
require_once 'FrontWP.php';
require_once 'ControllerWP.php';

WP_Addon_Settings::getInstance();
FrontWP::getInstance();


require_once 'autoloader.php';

ControllerWP::getInstance()->options_loader();

