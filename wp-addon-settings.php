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
		$this->ver  = '1.2.1';

		add_action( 'plugins_loaded', [ $this, 'admin_init' ] );

		add_action( 'admin_notices', [ $this, 'admin_notices' ], 11 );
		add_action( 'network_admin_notices', [ $this, 'admin_notices' ], 11 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ], 20 );

		add_action( 'plugins_loaded', function () {
			load_plugin_textdomain( 'wp-addon', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}, 9 );
	}

	public static function getInstance() {
		if ( static::$instance === null ) {
			static::$instance = new self();
		}

		return static::$instance;
	}

	public function __clone() {
	}

	public function __wakeup() {
	}

	/**
	 * style and scripts in wp-admin
	 *
	 * @param $page
	 */
	public function admin_assets( $page ) {
		if ( strpos( $page, $this->wp_plugin_slug ) === false ) {
			return;
		}

		wp_enqueue_style( $this->wp_plugin_slug,
			RW_PLUGIN_URL . 'assets/css/min/admin.min.css',
			false,
			$this->ver,
			'all' );
	}

	public function admin_notices() {
		$options = get_option( $this->wp_plugin_slug );
	}

	/**
	 * @see  http://codestarframework.com/documentation/#/fields?id=checkbox
	 */
	public function admin_init() {
		$this->wp_plugin_name = __( 'Wordpress Addon', 'wp-addon' );
		$this->wp_plugin_slug = 'wp-addon';

		// Check core class for avoid errors
		if ( !class_exists( 'CSF' ) ) :
            return;
        endif;

        // Set a unique slug-like ID
        $prefix = $this->wp_plugin_slug;

        // Create options
        CSF::createOptions( $prefix, require_once 'settings/_options.php' );

        // General Settings
        CSF::createSection( $prefix, [
            'title'  => __( 'General Settings', 'wp-addon' ),
            'icon'   => 'fa fa-rocket',
            'fields' => require_once 'settings/main.php',
        ] );

        // Tweaks
        CSF::createSection( $prefix, [
            'title'  => __( 'Tweaks', 'wp-addon' ),
            'icon'   => 'fa fa-wordpress',
            'fields' => require_once 'settings/tweaks.php',
        ] );

        // Shortcodes and Widgets
        CSF::createSection( $prefix, [
            'title'  => __( 'Shortcodes and Widgets', 'wp-addon' ),
            'icon'   => 'fa fa-bolt',
            'fields' => require_once 'settings/widgets.php',
        ] );

        do_action( 'wp_addon_settings_section', $prefix );

        // Custom Code
        CSF::createSection( $prefix, [
            'title'  => __( 'Custom code', 'wp-addon' ),
            'icon'   => 'fa fa-code',
            'fields' => [
                [
                    'id'       => 'rw_header_css',
                    'type'     => 'code_editor',
                    'title'    => __( 'CSS Code in Header', 'wp-addon' ),
                    'settings' => [
                        'theme' => 'monokai',
                        'mode'  => 'css',
                    ],
                    'sanitize' => false,
                ],
                [
                    'id'       => 'rw_header_html',
                    'type'     => 'code_editor',
                    'title'    => __( 'Any HTML code or Analytics code in header.', 'wp-addon' ),
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
                    'title'    => __( 'Any HTML code in footer.', 'wp-addon' ),
                    'settings' => [
                        'theme' => 'monokai',
                        'mode'  => 'htmlmixed',
                        //'mode'  => 'php',
                    ],
                    'default'  => '',
                    'sanitize' => false,
                ],
            ],// #fields
        ] );

        // BackUp
        CSF::createSection( $prefix, [
            'title'  => __( 'Backup Settings', 'wp-addon' ),
            'icon'   => 'fa fa-server',
            'fields' => [
                [
                    'title' => __( 'Download settings now', 'wp-addon' ),
                    'desc'  => __( 'You can get or set settings from backup' ),
                    'type'  => 'backup',
                ],
            ],
        ] );

	}
}
