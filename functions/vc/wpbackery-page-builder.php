<?php
/**
 *
 * @author: Aleksey Tikhomirov
 * @year: 2019-11-26
 */


/**
 * Remove “Edit with WPBakery Page Builder” from WordPress Admin Bar
 */
function vc_remove_frontend_links() {
    vc_disable_frontend(); // this will disable frontend editor
}
add_action( 'vc_after_init', 'vc_remove_frontend_links' );


/**
 * To completely remove “Edit with WPBakery Page Builder” link
 */
function vc_remove_wp_admin_bar_button() {
    remove_action( 'admin_bar_menu', array( vc_frontend_editor(), 'adminBarEditLink' ), 1000 );
}
add_action( 'vc_after_init', 'vc_remove_wp_admin_bar_button' );


function remove_admin_notice(){
    // remove visual composer notice
    remove_action( 'admin_notices', ['Vc_License','adminNoticeLicenseActivation',]);

    if(function_exists( 'vc_license()')) {
        $dev_environment = vc_license()->isDevEnvironment('local');
        vc_license()->isDevEnvironment('loc');
    }


   /* $prefix = 'wpb_js_';
    $name = 'js_composer_purchase_code';
    if(false === get_option($prefix . $name, false)) {
        update_option($prefix . $name, '$value');
    }*/

}
add_action('plugins_loaded', 'remove_admin_notice');