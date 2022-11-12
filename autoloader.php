<?php
/**
 * @year: 2019-03-27
 */

/**
 *  Include all php file in folder classes
 */
foreach ( glob( RW_PLUGIN_DIR . 'classes/*.php' ) as $file ){
    $file = basename($file);
    require_once RW_PLUGIN_DIR . 'classes/' . $file;
}

/**
 *  Include all php file in subfolder
 */
foreach ( glob( RW_PLUGIN_DIR . 'functions/*.php' ) as $file ){
    $file = basename($file);
    require RW_PLUGIN_DIR . 'functions/' . $file;
}


/**
 *  Include all php file in subfolder
 */
foreach ( glob( RW_PLUGIN_DIR . 'functions/*/*.php' ) as $file ){
   // $file = basename($file);
    include_once $file;
}

/**
 *  Include all php file in folder shortcodes
 */
foreach ( glob( RW_PLUGIN_DIR . 'shortcodes/*.php' ) as $file ){
    $file = basename($file);
    require_once RW_PLUGIN_DIR . 'shortcodes/' . $file;
}


