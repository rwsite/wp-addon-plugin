<?php
/*
 * Plugin Name:  #1 WordPress AddOn
 * Plugin URL:   https://rwsite.ru
 * Description:  Addon for WordPress, Contact Form 7 etc;
 * Version:      1.1.4
 * Text Domain:  wp-addon
 * Domain Path:  /languages
 * Author:       Aleksey Tikhomirov
 * Author URI:   https://rwsite.ru
 *
 * Tags: wordpress, addon, wp-addon, rw-addon
 * Requires at least: 4.6
 * Tested up to: 5.6.0
 * Requires PHP: 7.4+
 *
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


add_action( 'plugins_loaded', function(){
	load_plugin_textdomain( 'wp-addon', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
});

add_action( 'admin_head', function () {

	if ( 'plugins' === get_current_screen()->id && !class_exists( 'CSF' ) ){
		?>
        <div id="message" class="updated notice is-dismissible"><p>
                To work <b>#1 RW Wordpress AddOn</b> plugin, please download and install <a href="https://github.com/Codestar/codestar-framework" target="_blank">Codestar Framework</a>.
            </p></div>
		<?php
	}

});


// simple autoloader
require_once 'autoloader.php';


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
