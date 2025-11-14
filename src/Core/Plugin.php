<?php

namespace WpAddon\Core;

/**
 * Main Plugin class for initialization and constants
 */
class Plugin
{
    /**
     * Plugin file path
     */
    private string $file;

    /**
     * Plugin directory path
     */
    private string $dir;

    /**
     * Plugin URL
     */
    private string $url;

    /**
     * Plugin version
     */
    private string $version = '1.2.1';

    /**
     * Text domain
     */
    private string $textDomain = 'wp-addon';

    /**
     * Option service
     */
    private \WpAddon\Services\OptionService $optionService;

    /**
     * Asset service
     */
    private \WpAddon\Services\AssetService $assetService;

    /**
     * Media cleanup service
     */
    private \WpAddon\Services\MediaCleanupService $mediaCleanupService;

    /**
     * Constructor
     *
     * @param string $file Plugin file path
     */
    public function __construct(string $file)
    {
        $this->file = $file;
        $this->dir = plugin_dir_path($file);
        $this->url = plugin_dir_url($file);
    }

    /**
     * Initialize the plugin
     */
    public function init(): void
    {
        $this->defineConstants();
		$this->loadLocales();
        $this->loadDependencies();
        $this->addHooks();
    }


	private function loadLocales(): void {
		add_action( 'plugins_loaded', function () {
			$domain = 'wp-addon';
			$path = dirname( plugin_basename( RW_FILE ) ) . '/languages';
			load_plugin_textdomain( $domain, false, $path );
		}, 9 );
	}

    /**
     * Define plugin constants
     */
    private function defineConstants(): void
    {
        if (!defined('RW_LANG')) {
            define('RW_LANG', $this->textDomain);
        }

        if (!defined('RW_PLUGIN_DIR')) {
            define('RW_PLUGIN_DIR', $this->dir);
        }

        if (!defined('RW_PLUGIN_URL')) {
            define('RW_PLUGIN_URL', $this->url);
        }

        if (!defined('RW_FILE')) {
            define('RW_FILE', $this->file);
        }
    }

    /**
     * Load plugin dependencies
     */
    private function loadDependencies(): void
    {
        // Load settings
        require_once $this->dir . 'src/Config/wp-addon-settings.php';

        // Initialize services
        $this->optionService = new \WpAddon\Services\OptionService(RW_LANG);
        $this->assetService = new \WpAddon\Services\AssetService(RW_FILE, RW_PLUGIN_URL, RW_LANG, $this->version);
        $this->mediaCleanupService = new \WpAddon\Services\MediaCleanupService();

        // Load functions and modules
        $this->loadModules();
    }

    /**
     * Load and initialize modules from functions directory
     */
    private function loadModules(): void {
        foreach (glob($this->dir . 'functions/*.php') as $file) {
            require_once $file;
            $className = basename($file, '.php');
            if (class_exists($className) && is_subclass_of($className, 'WpAddon\Interfaces\ModuleInterface')) {
                // For complex modules inject dependencies
                if ($className === 'MediaCleanup') {
                    $module = new $className($this->mediaCleanupService);
                } else {
                    $module = new $className();
                }
                $module->init();
            }
        }

        foreach (glob($this->dir . 'functions/*/*.php') as $file) {
            require_once $file;
            $className = basename($file, '.php');
            if (class_exists($className) && is_subclass_of($className, 'WpAddon\Interfaces\ModuleInterface')) {
                $module = new $className();
                $module->init();
            }
        }
    }

    /**
     * Add plugin hooks
     */
    private function addHooks(): void
    {
        add_action('plugins_loaded', [$this, 'onPluginsLoaded']);
    }

    /**
     * Callback for plugins_loaded hook
     */
    public function onPluginsLoaded(): void
    {
        // Initialize settings
        if (class_exists('\WpAddon\WP_Addon_Settings')) {
            \WpAddon\WP_Addon_Settings::getInstance()->add_actions();
        }

        // Initialize front-end logic
        $frontWP = new \WpAddon\FrontWP($this->optionService, $this->assetService);
        $frontWP->add_actions();

        // Initialize controller
        $controllerWP = new \WpAddon\ControllerWP($this->optionService);
        $controllerWP->options_loader();
    }
}
