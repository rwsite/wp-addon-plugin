<?php

namespace WpAddon;

use WpAddon\Services\MediaCleanupService;

defined( 'ABSPATH' ) or exit;


class WP_Addon_Settings {

	private static $instance;
	public $file;
	public $path;
	public $url;
	public $ver;
	public $wp_plugin_name;
	public $wp_plugin_slug;

	private function __construct() {
		$this->file = RW_FILE;
		$this->path = RW_PLUGIN_DIR;
		$this->url  = RW_PLUGIN_URL;
		$this->ver  = '1.1.3';
	}

    public static function getInstance(): WP_Addon_Settings
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

	public function __clone() {
	}

	public function __wakeup() {
	}

    public function add_actions()
    {
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme'] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ], 20 );
    }

	/**
	 * style and scripts in wp-admin
	 *
	 * @param $page
	 */
	public function admin_assets( $page ) {
		if ( false === strpos( $page, $this->wp_plugin_slug ) ) {
			return;
		}

		wp_enqueue_style( $this->wp_plugin_slug,
			RW_PLUGIN_URL . 'assets/css/min/admin.min.css',
			false,
			$this->ver,
			'all' );
	}

	/**
	 * @see  http://codestarframework.com/documentation/#/fields?id=checkbox
	 */
	public function after_setup_theme() {

		// Check core class for avoid errors
		if ( !class_exists( 'CSF' ) ){
            return;
        }

        $this->wp_plugin_name = __('Wordpress Addon', 'wp-addon');
        $this->wp_plugin_slug = 'wp-addon';

        // Set a unique slug-like ID
        $prefix = $this->wp_plugin_slug;

        // Create options
        \CSF::createOptions($prefix, require_once __DIR__ . '/_options.php');

        // General Settings
        \CSF::createSection($prefix, [
            'title'  => __('General Settings', 'wp-addon'),
            'icon'   => 'fa fa-rocket',
            'fields' => require_once __DIR__ . '/main.php',
        ]);

        // Tweaks
        \CSF::createSection($prefix, [
            'title'  => __('Tweaks', 'wp-addon'),
            'icon'   => 'fa fa-wordpress',
            'fields' => require_once __DIR__ . '/tweaks.php',
        ]);

        // Media Cleanup
        \CSF::createSection($prefix, [
            'title'  => __('Media Cleanup', 'wp-addon'),
            'icon'   => 'fa fa-image',
            'description' => __('This section allows you to clean up unused image sizes to free up disk space. WordPress generates multiple sizes for each uploaded image, but if your theme or plugins don\'t use all of them, they take up unnecessary space. Use this tool to identify and remove such files.<br><br><strong>When to use:</strong> After changing themes, disabling plugins that generate custom sizes, or optimizing site performance.<br><br><strong>Precautions:</strong> Always create a backup before cleanup. Use "Preview Cleanup" first to see what will be deleted. The tool preserves original images and "scaled" versions (up to 2000px). Deleted files cannot be recovered!', 'wp-addon'),
            'fields' => [
                [
                    'id'      => 'cleanup_images',
                    'type'    => 'content',
                    'title'   => __('Clean up unused image sizes', 'wp-addon'),
                    'content' => '<p>' . sprintf(__('This will delete all image sizes except: %s. Files will be deleted permanently!', 'wp-addon'), implode(', ', MediaCleanupService::getRegisteredSizesStatic())) . '</p><button id="preview-cleanup-btn" class="button">' . __('Preview Cleanup', 'wp-addon') . '</button> <button id="cleanup-images-btn" class="button button-primary">' . __('Start Cleanup', 'wp-addon') . '</button><div id="cleanup-result"></div><script>jQuery(document).ready(function($){$("#preview-cleanup-btn").click(function(e){e.preventDefault();$("#cleanup-result").html("' . __('Loading preview...', 'wp-addon') . '");$.post(ajaxurl,{action:"wp_addon_cleanup_images_dry_run",nonce:"'.wp_create_nonce('cleanup_images').'"},function(r){$("#cleanup-result").html(r);});});$("#cleanup-images-btn").click(function(e){e.preventDefault();if(confirm("' . __('Are you sure? This action cannot be undone.', 'wp-addon') . '")){$("#cleanup-result").html("' . __('Processing...', 'wp-addon') . '");$.post(ajaxurl,{action:"wp_addon_cleanup_images",nonce:"'.wp_create_nonce('cleanup_images').'"},function(r){$("#cleanup-result").html(r);});}});});</script>',
                ],
            ],
        ]);

        // Shortcodes and Widgets
        \CSF::createSection($prefix, [
            'title'  => __('Shortcodes and Widgets', 'wp-addon'),
            'icon'   => 'fa fa-bolt',
            'fields' => require __DIR__ . '/wp-widgets.php',
        ]);

        do_action('wp_addon_settings_section', $prefix);

        // Custom Code
        \CSF::createSection($prefix, [
            'title'  => __('Custom code', 'wp-addon'),
            'icon'   => 'fa fa-code',
            'fields' => [
                [
                    'id'       => 'rw_header_css',
                    'type'     => 'code_editor',
                    'title'    => __('CSS Code in Header', 'wp-addon'),
                    'settings' => [
                        'theme' => 'mbo',
                        'mode'  => 'css',
                    ],
                    'sanitize' => false,
                ],
                [
                    'id'       => 'rw_header_html',
                    'type'     => 'code_editor',
                    'title'    => __('Any HTML code or Analytics code in header.',
                        'wp-addon'),
                    'settings' => [
                        'theme' => 'monokai',
                        'mode'  => 'htmlmixed',
                    ],
                    'default'  => '',
                    'sanitize' => false,
                ],
                [
                    'id'       => 'rw_footer_html',
                    'type'     => 'code_editor',
                    'title'    => __('Any HTML code in footer.', 'wp-addon'),
                    'settings' => [
                        'theme' => 'monokai',
                        //'mode'  => 'php',
                    ],
                    'default'  => '',
                    'sanitize' => false,
                ],
            ],// #fields
        ]);

        // BackUp
        \CSF::createSection($prefix, [
            'title'  => __('Backup Settings', 'wp-addon'),
            'icon'   => 'fa fa-server',
            'fields' => [
                [
                    'title' => __('Download settings now', 'wp-addon'),
                    'desc'  => __('You can get or set settings from backup'),
                    'type'  => 'backup',
                ],
            ],
        ]);
	}
}
