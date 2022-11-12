<?php
/**
 * Debug helpers. Unused in actions
 */


/**
 * Simple debug trace to wp-content/debug.log
 *
 * @usage _log( $var );
 */
if ( ! function_exists( '_log' ) ) {
    function _log( $log ) {

        if ( true == WP_DEBUG ) {
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
    }
}

add_action('mu_plugin_loaded', 'change_log_file_location');
function change_log_file_location(){
    ini_set( 'error_log', WP_CONTENT_DIR . '/hidden-debug.log' );
}