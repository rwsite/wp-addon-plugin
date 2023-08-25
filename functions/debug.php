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
        ob_start();
        echo '[' . date('d-M-Y h:i:s T') . '] ';
        var_export( $log );
        echo "\r\n";
        file_put_contents( ABSPATH . 'wp-content/debug.log', $data = ob_get_contents(), FILE_APPEND );
        return ob_get_clean();
    }
}


if ( ! function_exists( 'console_log' ) ) {
    function console_log($data){
        global $wp_query, $current_user;
        _log($data);

        if(is_bool($data)){
            $data = (int)$data;
        }

        if(empty($data)){
            $data = 'null';
        }

        $wp_query->debug_log = $data;
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
            if(!is_string($wp_query->debug_log)){
                $wp_query->debug_log = print_r( $wp_query->debug_log, true);
            } else {
                $wp_query->debug_log = "'$wp_query->debug_log'";
            }
            echo '<script type="text/javascript" name="woo2iiko_debugger">console.log({debug: \'wp-addon\'}, ' . $wp_query->debug_log . ')</script>';
            $wp_query->debug_showed = true;

            echo '<hr><h5 style="color:red;">DEBUG INFO</h5>';
            echo '<pre style="color:white; background: #0a0a0a; padding: 20px;">' . $wp_query->debug_log . '</pre>';
        }
    }
}

