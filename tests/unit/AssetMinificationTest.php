<?php
namespace WpAddon\Tests\Unit;

use WpAddon\Tests\TestCase;
use Mockery;

class AssetMinificationTest extends TestCase
{
    private $assetMinification;
    private $optionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->optionService = Mockery::mock('\\WpAddon\\Services\\OptionService');
        $this->assetMinification = new \AssetMinification($this->optionService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testProcessCssCreatesCacheFiles(): void
    {
        // Mock config
        $this->optionService->shouldReceive('getSetting')
            ->andReturnUsing(function($key, $default) {
                $config = [
                    'asset_minification_enabled' => true,
                    'asset_minify_css' => true,
                    'asset_minify_js' => false,
                    'asset_combine_css' => false,
                    'asset_combine_js' => false,
                    'asset_critical_css_enabled' => false,
                    'asset_defer_non_critical_css' => false,
                    'asset_exclude_css' => [],
                    'asset_exclude_js' => []
                ];
                return $config[$key] ?? $default;
            });

        // Mock WordPress globals
        global $wp_styles;
        $wp_styles = new \stdClass();
        $wp_styles->queue = ['test-style'];
        $wp_styles->registered = [
            'test-style' => (object) [
                'handle' => 'test-style',
                'src' => 'http://localhost/wp-content/themes/yootheme_child/css/test.css',
                'deps' => [],
                'ver' => '1.0.0',
                'args' => 'all'
            ]
        ];

        // Mock functions are defined in bootstrap

        // Initialize
        $this->assetMinification->init();

        // Process assets
        $this->assetMinification->processAssets();

        // Check if cache file was created
        $cacheDir = WP_CONTENT_DIR . '/cache/assets/';
        $files = glob($cacheDir . 'css-test-style-*.gz');

        $this->assertNotEmpty($files, 'Cache file should be created');

        // Clean up
        foreach ($files as $file) {
            unlink($file);
        }
    }

    public function testProcessJsCreatesCacheFiles(): void
    {
        // Mock config for JS
        $this->optionService->shouldReceive('getSetting')
            ->andReturnUsing(function($key, $default) {
                $config = [
                    'asset_minification_enabled' => true,
                    'asset_minify_css' => false,
                    'asset_minify_js' => true,
                    'asset_combine_css' => false,
                    'asset_combine_js' => false,
                    'asset_critical_css_enabled' => false,
                    'asset_defer_non_critical_css' => false,
                    'asset_exclude_css' => [],
                    'asset_exclude_js' => []
                ];
                return $config[$key] ?? $default;
            });

        // Mock WordPress globals
        global $wp_scripts;
        $wp_scripts = new \stdClass();
        $wp_scripts->queue = ['test-script'];
        $wp_scripts->registered = [
            'test-script' => (object) [
                'handle' => 'test-script',
                'src' => 'http://localhost/wp-content/themes/yootheme_child/js/test.js',
                'deps' => [],
                'ver' => '1.0.0',
                'args' => false
            ]
        ];

        // Mock functions are defined in bootstrap

        // Create test JS file
        $jsContent = 'function test() { return true; }';
        file_put_contents(WP_CONTENT_DIR . '/themes/yootheme_child/js/test.js', $jsContent);

        // Initialize
        $this->assetMinification->init();

        // Process assets
        $this->assetMinification->processAssets();

        // Check if cache file was created
        $cacheDir = WP_CONTENT_DIR . '/cache/assets/';
        $files = glob($cacheDir . 'js-test-script-*.gz');

        $this->assertNotEmpty($files, 'Cache file should be created');

        // Clean up
        foreach ($files as $file) {
            unlink($file);
        }
        unlink(WP_CONTENT_DIR . '/themes/yootheme_child/js/test.js');
    }
}
