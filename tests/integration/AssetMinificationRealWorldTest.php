<?php
namespace WpAddon\Tests\Integration;

use WpAddon\Tests\TestCase;
use Brain\Monkey;

class AssetMinificationRealWorldTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        // Минимальные моки для WordPress функций
        Monkey\Functions\stubs([
            'is_admin' => false,
            'wp_doing_ajax' => false,
            'wp_enqueue_style' => null,
            'wp_enqueue_script' => null,
            'wp_dequeue_style' => null,
            'wp_dequeue_script' => null,
            'wp_add_inline_script' => null,
            'add_action' => null,
            'site_url' => $this->getMockWordPressPaths()['site_url'],
            'content_url' => $this->getMockWordPressPaths()['content_url'],
            'plugin_dir_path' => $this->getMockWordPressPaths()['plugin_dir_path'],
            'get_template_directory' => $this->getMockWordPressPaths()['get_template_directory'],
        ]);
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testPluginIsActivated(): void
    {
        // Чекпоинт 1: Плагин активирован
        $active_plugins = get_option('active_plugins', []);
        $this->assertContains('wp-addon-plugin/wp-addon-plugin.php', $active_plugins);
    }

    public function testPluginConstantsAreDefined(): void
    {
        // Чекпоинт 2: Константы определены
        $this->assertTrue(defined('RW_PLUGIN_DIR'));
        $this->assertTrue(defined('RW_PLUGIN_URL'));
        $this->assertTrue(defined('RW_FILE'));
    }

    public function testAssetMinificationModuleLoads(): void
    {
        // Чекпоинт 3: Класс загружается
        $this->assertTrue(class_exists('AssetMinification'));
        $this->assertTrue(is_subclass_of('AssetMinification', 'WpAddon\\Interfaces\\ModuleInterface'));
    }

    public function testAssetMinificationConfigLoads(): void
    {
        // Чекпоинт 4: Конфиг загружается
        $optionService = new \WpAddon\Services\OptionService();
        $assetMinification = new AssetMinification($optionService);

        Monkey\Functions\stubs([
            'get_option' => function($key) {
                return ['wp-addon' => [
                    'asset_minification_enabled' => true,
                    'asset_minify_css' => true,
                    'asset_minify_js' => true,
                    'asset_combine_css' => true,
                    'asset_combine_js' => true,
                    'asset_critical_css_enabled' => true,
                    'asset_defer_non_critical_css' => true,
                    'asset_exclude_css' => 'admin-bar,dashicons',
                    'asset_exclude_js' => 'jquery,jquery-core'
                ]];
            }
        ]);

        $assetMinification->init();

        $reflection = new \ReflectionClass($assetMinification);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $config = $configProperty->getValue($assetMinification);

        $this->assertIsArray($config);
        $this->assertTrue($config['enabled']);
        $this->assertTrue($config['minify_css']);
    }

    public function testCacheDirectoryExists(): void
    {
        // Чекпоинт 5: Директория кэша существует
        $cacheDir = WP_CONTENT_DIR . '/cache/assets/';
        $this->assertTrue(is_dir($cacheDir));
        $this->assertTrue(is_writable($cacheDir));
    }

    public function testAssetOptimizationServiceWorks(): void
    {
        // Чекпоинт 6: Сервис оптимизации работает
        $config = [
            'cache_dir' => WP_CONTENT_DIR . '/cache/assets/',
            'version_salt' => 'test',
            'minify_css' => true,
            'minify_js' => true,
            'combine_css' => true,
            'combine_js' => true,
            'critical_css_enabled' => true,
            'exclude_css' => [],
            'exclude_js' => []
        ];

        $service = new \WpAddon\Services\AssetOptimizationService($config);
        $this->assertInstanceOf('WpAddon\\Services\\AssetOptimizationService', $service);

        // Тест минификации CSS
        $css = '.test { color: red; font-size: 14px; }';
        $minified = $service->minifyCss($css);
        $this->assertStringNotContains('\n', $minified);
        $this->assertStringNotContains('  ', $minified);

        // Тест минификации JS
        $js = 'function test() { return true; }';
        $minifiedJs = $service->minifyJs($js);
        $this->assertStringNotContains('\n', $minifiedJs);

        // Тест генерации версии
        $version1 = $service->generateVersion('content1');
        $version2 = $service->generateVersion('content2');
        $this->assertNotEquals($version1, $version2);
        $this->assertIsString($version1);
    }

    public function testCssMinificationCreatesCacheFile(): void
    {
        // Чекпоинт 7: Минификация CSS создает файл кэша
        $config = [
            'cache_dir' => WP_CONTENT_DIR . '/cache/assets/',
            'version_salt' => 'test',
            'minify_css' => true,
            'minify_js' => true,
            'combine_css' => true,
            'combine_js' => true,
            'critical_css_enabled' => true,
            'exclude_css' => [],
            'exclude_js' => []
        ];

        $service = new \WpAddon\Services\AssetOptimizationService($config);
        $css = '.test { color: red; font-size: 14px; }';
        $version = $service->generateVersion($css);
        $cacheKey = 'test-css-' . $version;
        $service->saveToCache($cacheKey, $css);

        $cacheFile = $config['cache_dir'] . $cacheKey . '.gz';
        $this->assertTrue(file_exists($cacheFile));

        // Проверить содержимое
        $cachedContent = $service->getFromCache($cacheKey);
        $this->assertEquals($css, $cachedContent);

        // Очистить
        unlink($cacheFile);
    }

    public function testJsMinificationCreatesCacheFile(): void
    {
        // Чекпоинт 8: Минификация JS создает файл кэша
        $config = [
            'cache_dir' => WP_CONTENT_DIR . '/cache/assets/',
            'version_salt' => 'test',
            'minify_css' => true,
            'minify_js' => true,
            'combine_css' => true,
            'combine_js' => true,
            'critical_css_enabled' => true,
            'exclude_css' => [],
            'exclude_js' => []
        ];

        $service = new \WpAddon\Services\AssetOptimizationService($config);
        $js = 'function test() { return true; }';
        $version = $service->generateVersion($js);
        $cacheKey = 'test-js-' . $version;
        $service->saveToCache($cacheKey, $js);

        $cacheFile = $config['cache_dir'] . $cacheKey . '.gz';
        $this->assertTrue(file_exists($cacheFile));
        $cachedContent = $service->getFromCache($cacheKey);
        $this->assertEquals($js, $cachedContent);
        unlink($cacheFile);
    }

    public function testCriticalCssExtraction(): void
    {
        // Чекпоинт 9: Извлечение critical CSS работает
        $optionService = new \WpAddon\Services\OptionService();
        $assetMinification = new AssetMinification($optionService);

        Monkey\Functions\stubs([
            'get_option' => function($key) {
                return ['wp-addon' => ['asset_critical_css_enabled' => true]];
            }
        ]);

        $assetMinification->init();

        ob_start();
        $assetMinification->injectCriticalCss();
        $output = ob_get_clean();

        $this->assertStringContains('<style id="wp-addon-critical-css">', $output);
        $this->assertStringContains('.header', $output);
    }

    public function testAssetProcessingHooksAreRegistered(): void
    {
        // Чекпоинт 10: Хуки зарегистрированы
        $optionService = new \WpAddon\Services\OptionService();
        $assetMinification = new AssetMinification($optionService);
        $assetMinification->init();

        global $wp_filter;
        $this->assertArrayHasKey('wp_enqueue_scripts', $wp_filter);
        $this->assertArrayHasKey('wp_head', $wp_filter);
    }

    public function testHttpRequestShowsOptimization(): void
    {
        // Чекпоинт 11: HTTP запрос показывает оптимизацию
        $url = site_url('/');

        if (function_exists('wp_remote_get')) {
            $response = wp_remote_get($url);
            if (!is_wp_error($response) && $response['response']['code'] === 200) {
                $html = wp_remote_retrieve_body($response);

                $hasCriticalCss = strpos($html, 'wp-addon-critical-css') !== false;
                $hasOptimizedAssets = strpos($html, 'cache/assets/') !== false;

                $this->assertTrue($hasCriticalCss || $hasOptimizedAssets,
                    'Page should show signs of AssetMinification optimization');
            }
        }
    }
}
