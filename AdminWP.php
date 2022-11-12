<?php

/**
 * @year: 2019-03-27
 */
defined( 'ABSPATH' ) or exit;


class AdminWP {

	public $file;
	public $path;
	public $url;
	public $ver;

	public $wp_plugin_name;
	public $wp_plugin_slug;

	private static $instance;

	public static function getInstance() {
		if ( static::$instance === null ) {
			static::$instance = new self();
		}
		return static::$instance;
	}

	public function __clone() {}

	public function __wakeup() {}

	private function __construct() {

		$this->file = RW_FILE;
		$this->path = RW_PLUGIN_DIR;
		$this->url  = RW_PLUGIN_URL;
		$this->ver  = '1.1.3';

		add_action( 'plugins_loaded', [ $this, 'admin_init' ] );

		add_action( 'admin_notices', [ $this, 'admin_notices' ], 11 );
		add_action( 'network_admin_notices', [ $this, 'admin_notices' ], 11 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ], 20 );
	}


	/**
	 * style and scripts in wp-admin
	 *
	 * @param $page
	 */
	public function admin_assets( $page ) {
		if ( strpos( $page, $this->wp_plugin_slug ) !== false ) {
			// wp_enqueue_script($this->wp_plugin_slug);
		}
		wp_enqueue_style( 'wp-addon', RW_PLUGIN_URL . 'assets/css/min/admin.min.css', false, $this->ver, 'all' );
	}

	public function admin_notices() {
		$options = get_option( $this->wp_plugin_slug );
	}

	/**
	 * @see  http://codestarframework.com/documentation/#/fields?id=checkbox
	 */
	public function admin_init() {

		$this->wp_plugin_name = __('Wordpress Addon', 'wp-addon' );
		$this->wp_plugin_slug = 'wp-addon';

		// Check core class for avoid errors
		if ( class_exists( 'CSF' ) ) :

			// Set a unique slug-like ID
			$prefix = $this->wp_plugin_slug;

			// Create options
			CSF::createOptions( $prefix, [

				// framework title
				'framework_title'    => $this->wp_plugin_name . ' <small>' . __( 'by Aleksey Tihomirov',
						'wp-addon' ) . '</small>',
				'framework_class'    => 'wp-addon',

				// menu settings
				'menu_title'         => $this->wp_plugin_name,
				'menu_slug'          => $this->wp_plugin_slug,
				'menu_type'          => 'menu',
				'menu_capability'    => 'manage_options',
				'menu_icon'          => 'dashicons-heart',
				'menu_position'      => null,
				'menu_hidden'        => false,
				'menu_parent'        => '',

				// menu extras
				'show_bar_menu'      => false,
				'show_sub_menu'      => true,
				'show_network_menu'  => true,
				'show_in_customizer' => false,

				'show_search'             => true,
				'show_reset_all'          => true,
				'show_reset_section'      => true,
				'show_footer'             => true,
				'show_all_options'        => true,
				'sticky_header'           => true,
				'save_defaults'           => true,
				'ajax_save'               => true,


				// admin bar menu settings
				'admin_bar_menu_icon'     => 'dashicons-heart',
				'admin_bar_menu_priority' => 80,

				// footer
				'footer_text'             => __( 'With love by <span style="color: #ED4301;">A</span> Tikhomirov',
					'wp-addon' ),
				'footer_after'            => '',
				'footer_credit'           => '',

				'theme'                   => 'light',

				// database model
				'database'                => '', // options, transient, theme_mod, network
				'transient_time'          => 0,

				// contextual help
				'contextual_help'         => [],
				'contextual_help_sidebar' => '',
				//
				'enqueue_webfont'         => true,
				'async_webfont'           => false,
				// others
				'output_css'              => true,
				'class'                   => '',
			] );


			/**
			 * Example require settings
			 */

			$general = null;
			require_once 'settings/general.php';// set general


			// General Settings
			CSF::createSection( $prefix, $general );

			// Tweaks
			CSF::createSection( $prefix, [
				'title'  => __( 'Tweaks', 'wp-addon' ),
				'icon'   => 'fa fa-wordpress',
				'fields' => [
					[
						'id'      => 'wptweaker_setting_1',
						'type'    => 'switcher',
						'title'   => __( 'Remove WP-Version in Header', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_2',
						'type'    => 'switcher',
						'title'   => __( 'WP-Emojis deacivation', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_3',
						'type'    => 'switcher',
						'title'   => __( 'Remove Windows Live Writer', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_4',
						'type'    => 'switcher',
						'title'   => __( 'Remove RSD-Link', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_5',
						'type'    => 'switcher',
						'title'   => __( 'Remove RSS links', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_6',
						'type'    => 'switcher',
						'title'   => __( 'Remove shortlink in the header', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_7',
						'type'    => 'switcher',
						'title'   => __( 'Remove adjacent links to posts in the header', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_8',
						'type'    => 'switcher',
						'title'   => __( 'Set limit post revisions to 5', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_9',
						'type'    => 'switcher',
						'title'   => __( 'Block http-requests by plugins/themes', 'wp-addon' ),
						'default' => false,
					],
					[
						'id'      => 'wptweaker_setting_10',
						'type'    => 'switcher',
						'title'   => __( 'Disable heartbeat', 'wp-addon' ),
						'default' => false,
					],
					[
						'id'      => 'wptweaker_setting_11',
						'type'    => 'switcher',
						'title'   => __( 'Remove jQuery Migrate', 'wp-addon' ),
						'default' => false,
					],
					[
						'id'      => 'wptweaker_setting_12',
						'type'    => 'switcher',
						'title'   => __( 'Disable new themes on major WP updates', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_13',
						'type'    => 'switcher',
						'title'   => __( 'Disable XML-RPC', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_14',
						'type'    => 'switcher',
						'title'   => __( 'Remove post by email function', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_15',
						'type'    => 'switcher',
						'title'   => __( 'Disable agressive update', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_16',
						'type'    => 'switcher',
						'title'   => __( 'Disable URL auto-linking in comments', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_17',
						'type'    => 'switcher',
						'title'   => __( 'Remove login-shake on errors', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_18',
						'type'    => 'switcher',
						'title'   => __( 'Empty WP-Trash every 14 days', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_19',
						'type'    => 'switcher',
						'title'   => __( 'Allow download types file: SVG, DOC, djv ..', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_20',
						'type'    => 'switcher',
						'title'   => __( 'Disable pingback from this site to this site', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_21',
						'type'    => 'switcher',
						'title'   => __( 'Hide adminbar in frontend for contributors, authors and subscribers',
							'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_22',
						'type'    => 'switcher',
						'title'   => __( 'Add VK and OK to user profile', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_23',
						'type'    => 'switcher',
						'title'   => __( 'Showing usages memory and time generate site page', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_24',
						'type'    => 'switcher',
						'title'   => __( 'Remove Standard WP widget', 'wp-addon' ),
						'default' => false,
					],
					[
						'id'      => 'wptweaker_setting_25',
						'type'    => 'switcher',
						'title'   => __( 'AutoRemove readme.html and license.txt files', 'wp-addon' ),
						'default' => false,
					],
					[
						'id'      => 'wptweaker_setting_26',
						'type'    => 'switcher',
						'title'   => __( 'Add notice for posts if they have status "pending" ', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_27',
						'type'    => 'switcher',
						'title'   => __( 'Disable select taxonomy in top of metabox in post-edit page.', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_28',
						'type'    => 'switcher',
						'title'   => __( 'If posts have status "pending" show numbers it in menu', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_29',
						'type'    => 'switcher',
						'title'   => __( 'Showing message "Update wordpress" only admin', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_30',
						'type'    => 'switcher',
						'title'   => __( 'Repalse [...] to "Read more ..." for posts', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_31',
						'type'    => 'switcher',
						'title'   => __( 'Allow shortcode in "Text" widget', 'wp-addon' ),
						'default' => true,
					],
					[
						'id'      => 'wptweaker_setting_32',
						'type'    => 'switcher',
						'title'   => __( 'Get jquery from google cloud', 'wp-addon' ),
						'default' => false,
					],
				],
			] );

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
							'theme' => 'mbo',
							'mode'  => 'css',
						],
						// 'default'  => '.body{ background: rebeccapurple; }',
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
					],
					[
						'id'       => 'rw_footer_html',
						'type'     => 'code_editor',
						'title'    => __( 'Any HTML code in footer.', 'wp-addon' ),
						'settings' => [
							'theme' => 'monokai',
							'mode'  => 'htmlmixed',
						],
						'default'  => '',
					],
				]// #fields
			] );

			// Shortcodes and Widgets
			CSF::createSection( $prefix, [
				'title'  => __( 'Shortcodes and Widgets', 'wp-addon' ),
				'icon'   => 'fa fa-bolt',
				'fields' => [
					[
						'type'    => 'content',
						'content' => __( 'Functional in development', 'wp-addon' ),
					],
					[   // Shortcodes
						'id'       => 'components',
						'type'     => 'tabbed',
						'title'    => __( 'Components', 'wp-addon' ),
						'subtitle' => __( 'Capability with WPBakery Page Builder', 'wp-addon' ),
						'tabs'     => [
							[
								'title'  => __( 'Sidebars', 'wp-addon' ),
								'icon'   => 'fa fa-desktop',
								'fields' => [
									[
										'id'      => 'add_sidebar_1',
										'type'    => 'switcher',
										'title'   => __( 'Additional sidebar 1', 'wp-addon' ),
										'default' => true,
									],
									[
										'id'      => 'add_sidebar_2',
										'type'    => 'switcher',
										'title'   => __( 'Additional sidebar 2', 'wp-addon' ),
										'default' => true,
									],
									[
										'id'      => 'add_sidebar_3',
										'type'    => 'switcher',
										'title'   => __( 'Additional sidebar 3', 'wp-addon' ),
										'default' => true,
									],
								],
							],
							[
								'title'  => __( 'Widgets', 'rw-addon' ),
								'icon'   => 'fa fa-connectdevelop',
								'fields' => [
									[
										'id'      => 'archive_widget',
										'type'    => 'switcher',
										'title'   => __( 'Yearly archive widget', 'wp-addon' ),
										'default' => true,
									],
								],
							],
						],
					],
				],
			] );

			// Dashboard widgets
			CSF::createSection( $prefix, [
				'title'  => __( 'Admin Dashboard Settings', 'wp-addon' ),
				'icon'   => 'fa fa-tachometer',
				'fields' => [
					[
						'type'    => 'subheading',
						'content' => __( 'Add to Dashboard additional widgets and other functional', 'wp-addon' ),
					],
					[   // maintenance
						'id'      => 'enable_maintenance',
						'type'    => 'switcher',
						'title'   => __( 'Maintenance Mode', 'wp-addon' ),
						'default' => false,
					],
					[   // add_clone_widget
						'id'      => 'add_clone_widget',
						'type'    => 'switcher',
						'title'   => __( 'Enable Duplicate widgets function', 'wp-addon' ),
						'default' => true,
					],
					[   // Sidebars
						'id'     => 'dashboard_widgets',
						'type'   => 'fieldset',
						'title'  => __( 'Dashboard widgets', 'wp-addon' ),
						'fields' => [
							[
								'id'      => 'change_glance_widget',
								'type'    => 'switcher',
								'title'   => __( 'Add all types at a glance widget', 'wp-addon' ),
								'default' => true,
							],
							[
								'id'      => 'dashboard_plugin_list',
								'type'    => 'switcher',
								'title'   => __( 'Plugins list', 'wp-addon' ),
								'default' => true,
							],
							[
								'id'      => 'dashboard_server_info',
								'type'    => 'switcher',
								'title'   => __( 'Server info', 'wp-addon' ),
								'default' => true,
							],
							[
								'id'      => 'dashboard_role_list',
								'type'    => 'switcher',
								'title'   => __( 'Users role info', 'wp-addon' ),
								'default' => true,
							],
						],
					],
				],
			] );

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

		endif;
	}

}
