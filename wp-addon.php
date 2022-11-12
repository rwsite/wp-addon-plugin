<?php
/**
 * Plugin Name:  #1 RW Wordpress AddOn
 * Plugin URL:   https://rwsite.ru
 * Description:  Addon for Wordpress, Contact Form 7 etc;
 * Version:      1.1.3
 * Text Domain:  wp-addon
 * Domain Path:  /languages/
 * Author:       Aleksey Tikhomirov
 * Author URI:   https://rwsite.ru
 *
 * Tags: wordpress, addon, wp-addon, rw-addon
 * Requires at least: 4.6
 * Tested up to: 5.3.0
 * Requires PHP: 7.0+
 *
 * @package WordPress Addon
 * docs:
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/*
|--------------------------------------------------------------------------
| CONSTANTS
|--------------------------------------------------------------------------
*/

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

// simple autoloader
require_once 'autoloader.php';

add_action( 'init', function () {
	load_plugin_textdomain( 'wp-addon', false, dirname( plugin_basename( RW_FILE ) ) . '/languages/');
});

// ControllerWP
require_once 'ControllerWP.php';
require_once 'AdminWP.php';
require_once 'FrontWP.php';

ControllerWP::getInstance()->options_loader();

if( is_admin() ) {
    AdminWP::getInstance();
} else {
    FrontWP::getInstance();
}