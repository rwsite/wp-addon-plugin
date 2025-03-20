<?php
/*
 * Plugin Name:  # WordPress Addon
 * Plugin URL:   https://rwsite.ru
 * Description:  Addon for WordPress;
 * Version:      1.2.1
 * Text Domain:  wp-addon
 * Domain Path:  languages
 * Author:       Aleksey Tikhomirov
 * Author URI:   https://rwsite.ru
 *
 * Tags: wordpress, wp-addon,
 *
 * Requires at least: 4.6
 * Tested up to: 6.8.3
 * Requires PHP: 7.4+
 */


use classes\ControllerWP;
use classes\FrontWP;

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


require_once 'settings/wp-addon-settings.php';
require_once 'classes/FrontWP.php';
require_once 'classes/ControllerWP.php';


WP_Addon_Settings::getInstance()->add_actions();
FrontWP::getInstance()->add_actions();

require_once 'autoloader.php';

add_action('init', fn() => ControllerWP::getInstance()->options_loader());