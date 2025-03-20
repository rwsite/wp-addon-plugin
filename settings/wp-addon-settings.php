<?php


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
        add_action( 'init', function () {
            load_plugin_textdomain( 'wp-addon', false, dirname( plugin_basename( RW_FILE ) ) . '/languages' );
        }, 9 );

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
        CSF::createOptions($prefix, require_once '_options.php');

        // General Settings
        CSF::createSection($prefix, [
            'title'  => __('General Settings', 'wp-addon'),
            'icon'   => 'fa fa-rocket',
            'fields' => require_once 'main.php',
        ]);

        // Tweaks
        CSF::createSection($prefix, [
            'title'  => __('Tweaks', 'wp-addon'),
            'icon'   => 'fa fa-wordpress',
            'fields' => require_once 'tweaks.php',
        ]);

        // Shortcodes and Widgets
        CSF::createSection($prefix, [
            'title'  => __('Shortcodes and Widgets', 'wp-addon'),
            'icon'   => 'fa fa-bolt',
            'fields' => require 'wp-widgets.php',
        ]);

        do_action('wp_addon_settings_section', $prefix);

        // Custom Code
        CSF::createSection($prefix, [
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
        CSF::createSection($prefix, [
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
