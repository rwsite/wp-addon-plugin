<?php
/**
 * Debug helpers. Unused in actions
 */


if(!function_exists('change_log_file_location')):
    add_action('mu_plugin_loaded', 'change_log_file_location');
    function change_log_file_location(){
        ini_set( 'error_log', WP_CONTENT_DIR . '/hidden-debug.log' );
    }
endif;

/**
 * Simple debug trace to wp-content/debug.log
 * @usage _log( $var );
 */
if ( ! function_exists( '_log' ) ) {
    function _log( $log ) {

        if ( defined('WP_DEBUG') && WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        } else {
            ob_start();
            echo '[' . date('d-M-Y h:i:s T') . '] ';
            if ( is_array( $log ) || is_object( $log ) ) {
                print_r( $log );
            } else {
                echo( $log );
            }
            echo "\r\n";
            file_put_contents( ABSPATH . 'wp-content/log.log', $data = ob_get_contents(), FILE_APPEND );
            ob_end_clean();
        }

        return $data ?? $log;
    }
}


if ( ! function_exists( 'console_log' ) ) {
    function console_log($data){
        global $wp_query, $current_user;
        $wp_query->debug_log = _log($data);
        $wp_query->debug_showed = false;
        if (isset($current_user) && $current_user instanceof WP_User && $current_user->has_cap('manage_options')) {
            add_action('admin_head', 'show_in_console');
            add_action('admin_footer', 'show_in_console', 99);
            add_action('wp_head', 'show_in_console', 99);
            add_action('wp_footer', 'show_in_console');
        }
    }
    function show_in_console(){
        global $wp_query;
        if(!$wp_query->debug_showed) {
            echo '<script type="text/javascript" name="woo2iiko_debugger">console.log(\'wp debug\', ' . $wp_query->debug_log . '); </script>';
            $wp_query->debug_showed = true;
        }
    }
}

