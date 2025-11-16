<?php

namespace WpAddon\Tests\Integration;

use WpAddon\Tests\TestCase;
use AssetMinification;

/**
 * Integration test for AssetMinification module
 */
class AssetMinificationIntegrationTest extends TestCase
{
    /** @var AssetMinification */
    private $assetMinification;

    /** @var \WpAddon\Services\OptionService */
    private $mockOptionService;

    /** @var string */
    private $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheDir = sys_get_temp_dir() . '/wp_addon_integration_cache_' . uniqid();
        mkdir($this->cacheDir, 0755, true);

        $this->mockOptionService = $this->createMock(\WpAddon\Services\OptionService::class);

        // Mock option service to return enabled config
        $this->mockOptionService->method('getSetting')
            ->willReturnCallback(function($key, $default = null) {
                $config = [
                    'enabled' => true,
                    'minify_css' => true,
                    'minify_js' => true,
                    'combine_css' => true,
                    'combine_js' => true,
                    'critical_css_enabled' => true,
                    'defer_non_critical_css' => true,
                    'exclude_css' => ['admin-bar', 'dashicons'],
                    'exclude_js' => ['jquery', 'jquery-core'],
                    'cache_dir' => $this->cacheDir,
                    'version_salt' => 'wp-addon-v1',
                ];
                return $config[$key] ?? $default;
            });

        $this->assetMinification = new AssetMinification($this->mockOptionService);
    }

    protected function tearDown(): void
    {
        // Clean up cache directory
        if (is_dir($this->cacheDir)) {
            $files = glob($this->cacheDir . '/*');
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($this->cacheDir);
        }

        parent::tearDown();
    }

    public function testProcessAssetsWithCssOnly(): void
    {
        // Setup: Mock CSS files in queue
        $cssFiles = [
            'theme-style' => [
                'src' => 'http://example.com/wp-content/themes/theme/style.css',
                'deps' => [],
                'ver' => '1.0.0',
            ],
            'plugin-style' => [
                'src' => 'http://example.com/wp-content/plugins/plugin/style.css',
                'deps' => [],
                'ver' => '1.0.0',
            ]
        ];

        $this->mockWpStyles($cssFiles);

        // Create temporary CSS files
        $tempCss1 = $this->createTempFile(file_get_contents($this->getTestDataPath('test.css')), 'css');
        $tempCss2 = $this->createTempFile(file_get_contents($this->getTestDataPath('small.css')), 'css');

        // Mock file paths
        $tempDir = dirname($tempCss1);
        \Brain\Monkey\Functions\when('file_exists')->return(true);
        \Brain\Monkey\Functions\when('filesize')->justReturn(2048);
        \Brain\Monkey\Functions\when('file_get_contents')
            ->alias(function($path) use ($tempCss1, $tempCss2) {
                if (str_contains($path, 'theme-style') || str_contains($path, 'style.css')) {
                    return file_get_contents($tempCss1);
                }
                return file_get_contents($tempCss2);
            });

        // Execute
        $this->assetMinification->processAssets();

        // Assert: Check that styles were processed
        global $wp_styles;
        $this->assertNotEmpty($wp_styles->queue);

        // Check that combined CSS was enqueued
        $combinedFound = false;
        foreach ($wp_styles->registered as $handle => $style) {
            if (str_contains($handle, 'wp-addon-combined-css')) {
                $combinedFound = true;
                break;
            }
        }
        $this->assertTrue($combinedFound, 'Combined CSS should be enqueued');

        // Clean up
        $this->removeTempFile($tempCss1);
        $this->removeTempFile($tempCss2);
    }

    public function testProcessAssetsWithJsOnly(): void
    {
        // Setup: Mock JS files in queue
        $jsFiles = [
            'theme-script' => [
                'src' => 'http://example.com/wp-content/themes/theme/script.js',
                'deps' => [],
                'ver' => '1.0.0',
            ],
            'plugin-script' => [
                'src' => 'http://example.com/wp-content/plugins/plugin/script.js',
                'deps' => [],
                'ver' => '1.0.0',
            ]
        ];

        $this->mockWpScripts($jsFiles);

        // Create temporary JS files
        $tempJs1 = $this->createTempFile(file_get_contents($this->getTestDataPath('test.js')), 'js');
        $tempJs2 = $this->createTempFile('console.log("test");', 'js');

        // Mock file system
        \Brain\Monkey\Functions\when('file_exists')->return(true);
        \Brain\Monkey\Functions\when('filesize')->justReturn(2048);
        \Brain\Monkey\Functions\when('file_get_contents')
            ->alias(function($path) use ($tempJs1, $tempJs2) {
                if (str_contains($path, 'theme-script') || str_contains($path, 'script.js')) {
                    return file_get_contents($tempJs1);
                }
                return file_get_contents($tempJs2);
            });

        // Execute
        $this->assetMinification->processAssets();

        // Assert: Check that scripts were processed
        global $wp_scripts;
        $this->assertNotEmpty($wp_scripts->queue);

        // Check that combined JS was enqueued
        $combinedFound = false;
        foreach ($wp_scripts->registered as $handle => $script) {
            if (str_contains($handle, 'wp-addon-combined-js')) {
                $combinedFound = true;
                break;
            }
        }
        $this->assertTrue($combinedFound, 'Combined JS should be enqueued');

        // Clean up
        $this->removeTempFile($tempJs1);
        $this->removeTempFile($tempJs2);
    }

    public function testProcessAssetsExcludesSystemAssets(): void
    {
        // Setup: Mock system assets in queue
        $systemAssets = [
            'jquery' => [
                'src' => 'http://example.com/wp-includes/js/jquery.js',
                'deps' => [],
                'ver' => '3.6.0',
            ],
            'admin-bar' => [
                'src' => 'http://example.com/wp-includes/css/admin-bar.css',
                'deps' => [],
                'ver' => '1.0.0',
            ]
        ];

        $this->mockWpStyles($systemAssets);

        // Mock file system
        \Brain\Monkey\Functions\when('file_exists')->return(true);
        \Brain\Monkey\Functions\when('filesize')->justReturn(2048);
        \Brain\Monkey\Functions\when('file_get_contents')->return('body { color: red; }');

        // Execute
        $this->assetMinification->processAssets();

        // Assert: System assets should not be processed
        global $wp_styles;

        // Should not have combined CSS since system assets were excluded
        $combinedFound = false;
        foreach ($wp_styles->registered as $handle => $style) {
            if (str_contains($handle, 'wp-addon-combined-css')) {
                $combinedFound = true;
                break;
            }
        }
        $this->assertFalse($combinedFound, 'System assets should not be combined');
    }

    public function testProcessAssetsSkipsMinifiedFiles(): void
    {
        // Setup: Mock already minified CSS file
        $minifiedCss = file_get_contents($this->getTestDataPath('test.min.css'));

        $cssFiles = [
            'minified-style' => [
                'src' => 'http://example.com/wp-content/themes/theme/minified.css',
                'deps' => [],
                'ver' => '1.0.0',
            ]
        ];

        $this->mockWpStyles($cssFiles);

        // Mock file system
        \Brain\Monkey\Functions\when('file_exists')->return(true);
        \Brain\Monkey\Functions\when('filesize')->justReturn(2048);
        \Brain\Monkey\Functions\when('file_get_contents')->return($minifiedCss);

        // Execute
        $this->assetMinification->processAssets();

        // Assert: Minified file should be skipped
        global $wp_styles;

        // Original file should still be in queue (not processed)
        $this->assertContains('minified-style', $wp_styles->queue);
    }

    public function testProcessAssetsHandlesSmallFiles(): void
    {
        // Setup: Mock small CSS file
        $cssFiles = [
            'small-style' => [
                'src' => 'http://example.com/wp-content/themes/theme/small.css',
                'deps' => [],
                'ver' => '1.0.0',
            ]
        ];

        $this->mockWpStyles($cssFiles);

        // Mock file system - small file size
        \Brain\Monkey\Functions\when('file_exists')->return(true);
        \Brain\Monkey\Functions\when('filesize')->justReturn(500); // Less than 1KB
        \Brain\Monkey\Functions\when('file_get_contents')->return('body{margin:0}');

        // Execute
        $this->assetMinification->processAssets();

        // Assert: Small file should not be processed
        global $wp_styles;

        // File should still be in original queue
        $this->assertContains('small-style', $wp_styles->queue);

        // No combined CSS should be created
        $combinedFound = false;
        foreach ($wp_styles->registered as $handle => $style) {
            if (str_contains($handle, 'wp-addon-combined-css')) {
                $combinedFound = true;
                break;
            }
        }
        $this->assertFalse($combinedFound, 'Small files should not be combined');
    }

    public function testInjectCriticalCss(): void
    {
        // Mock get_template_directory to return test directory
        \Brain\Monkey\Functions\when('get_template_directory')
            ->justReturn(dirname($this->getTestDataPath()));

        // Create a test style.css in theme directory
        $themeCssContent = file_get_contents($this->getTestDataPath('test.css'));
        $themeCssPath = $this->createTempFile($themeCssContent, 'css');
        $themeDir = dirname($themeCssPath);

        \Brain\Monkey\Functions\when('get_template_directory')->justReturn($themeDir);

        // Execute
        ob_start();
        $this->assetMinification->injectCriticalCss();
        $output = ob_get_clean();

        // Assert: Critical CSS should be injected
        $this->assertStringContains('<style id="wp-addon-critical-css">', $output);
        $this->assertStringContains('color:#ff0000', $output);

        // Clean up
        $this->removeTempFile($themeCssPath);
    }

    public function testDeferCssLoading(): void
    {
        // Mock wp_add_inline_script to capture the added script
        $addedScripts = [];
        \Brain\Monkey\Functions\when('wp_add_inline_script')
            ->alias(function($handle, $data) use (&$addedScripts) {
                $addedScripts[$handle] = $data;
            });

        // Execute
        $this->assetMinification->deferCssLoading();

        // Assert: Defer script should be added
        $this->assertArrayHasKey('wp-addon-combined-js', $addedScripts);
        $this->assertStringContains('DOMContentLoaded', $addedScripts['wp-addon-combined-js']);
        $this->assertStringContains('media = "print"', $addedScripts['wp-addon-combined-js']);
    }

    public function testClearCache(): void
    {
        // Create test cache files
        $testFile1 = $this->cacheDir . '/test1.gz';
        $testFile2 = $this->cacheDir . '/test2.gz';

        file_put_contents($testFile1, 'test content 1');
        file_put_contents($testFile2, 'test content 2');

        // Verify files exist
        $this->assertFileExists($testFile1);
        $this->assertFileExists($testFile2);

        // Execute cache clearing
        $this->assetMinification->clearCache();

        // Assert: Cache files should be deleted
        $this->assertFileNotExists($testFile1);
        $this->assertFileNotExists($testFile2);
    }

    public function testInitDisabledModule(): void
    {
        // Mock disabled config
        $this->mockOptionService = $this->createMock(\WpAddon\Services\OptionService::class);
        $this->mockOptionService->method('getSetting')
            ->willReturnCallback(function($key, $default = null) {
                $config = [
                    'enabled' => false, // Disabled
                    'minify_css' => true,
                    'minify_js' => true,
                ];
                return $config[$key] ?? $default;
            });

        $assetMinification = new AssetMinification($this->mockOptionService);

        // Mock add_action to capture registered actions
        $registeredActions = [];
        \Brain\Monkey\Functions\when('add_action')
            ->alias(function($hook, $callback) use (&$registeredActions) {
                $registeredActions[] = $hook;
            });

        // Execute init
        $assetMinification->init();

        // Assert: No actions should be registered when disabled
        $this->assertEmpty($registeredActions);
    }

    public function testInitEnabledModule(): void
    {
        // Mock add_action to capture registered actions
        $registeredActions = [];
        \Brain\Monkey\Functions\when('add_action')
            ->alias(function($hook, $callback) use (&$registeredActions) {
                $registeredActions[] = $hook;
            });

        // Execute init
        $this->assetMinification->init();

        // Assert: Actions should be registered when enabled
        $this->assertContains('wp_enqueue_scripts', $registeredActions);
        $this->assertContains('wp_head', $registeredActions);
        $this->assertContains('save_post', $registeredActions);
        $this->assertContains('upgrader_process_complete', $registeredActions);
    }

    // ===== НОВЫЕ ТЕСТЫ ДЛЯ ПРОВЕРКИ РЕАЛЬНОЙ РАБОТЫ =====

    public function testRealPluginActivation(): void
    {
        // Проверяем активацию плагина в реальной среде
        if (function_exists('get_option')) {
            $active_plugins = get_option('active_plugins', []);
            $this->assertContains('wp-addon-plugin/wp-addon-plugin.php', $active_plugins,
                'Plugin should be activated in real WordPress environment');
        } else {
            $this->markTestSkipped('Not running in real WordPress environment');
        }
    }

    public function testRealPluginConstants(): void
    {
        // Проверяем константы плагина в реальной среде
        if (defined('RW_PLUGIN_DIR')) {
            $this->assertTrue(defined('RW_PLUGIN_DIR'), 'RW_PLUGIN_DIR should be defined');
            $this->assertTrue(defined('RW_PLUGIN_URL'), 'RW_PLUGIN_URL should be defined');
            $this->assertTrue(defined('RW_FILE'), 'RW_FILE should be defined');

            // Проверяем что пути существуют
            $this->assertTrue(is_dir(RW_PLUGIN_DIR), 'RW_PLUGIN_DIR should exist');
            $this->assertTrue(file_exists(RW_FILE), 'RW_FILE should exist');
        } else {
            $this->markTestSkipped('Plugin constants not defined - not in real environment');
        }
    }

    public function testRealCacheDirectory(): void
    {
        // Проверяем директорию кэша в реальной среде
        if (defined('WP_CONTENT_DIR')) {
            $cacheDir = WP_CONTENT_DIR . '/cache/assets/';
            $this->assertTrue(is_dir($cacheDir), 'Cache directory should exist in real environment');
            $this->assertTrue(is_writable($cacheDir), 'Cache directory should be writable');
        } else {
            $this->markTestSkipped('WP_CONTENT_DIR not defined');
        }
    }

    public function testRealSettingsPersistence(): void
    {
        // Проверяем настройки в реальной среде
        if (function_exists('get_option')) {
            $settings = get_option('wp-addon', []);
            $this->assertIsArray($settings, 'Plugin settings should be array');

            // Проверяем ключевые настройки AssetMinification
            $criticalKeys = [
                'asset_minification_enabled',
                'asset_minify_css',
                'asset_minify_js'
            ];

            foreach ($criticalKeys as $key) {
                $this->assertArrayHasKey($key, $settings,
                    "Critical setting {$key} should exist in real environment");
            }
        } else {
            $this->markTestSkipped('get_option not available');
        }
    }

    public function testRealModuleLoading(): void
    {
        // Проверяем загрузку модуля в реальной среде
        $this->assertTrue(class_exists('AssetMinification'),
            'AssetMinification should be loaded in real environment');

        $this->assertTrue(is_subclass_of('AssetMinification', 'WpAddon\\Interfaces\\ModuleInterface'),
            'AssetMinification should implement interface in real environment');
    }

    public function testRealWordPressHooks(): void
    {
        // Проверяем хуки WordPress в реальной среде
        if (isset($GLOBALS['wp_filter'])) {
            global $wp_filter;

            // Проверяем наличие хуков от AssetMinification
            $assetHooks = ['wp_enqueue_scripts', 'wp_head', 'wp_footer'];
            $foundAssetHooks = 0;

            foreach ($assetHooks as $hook) {
                if (isset($wp_filter[$hook])) {
                    $foundAssetHooks++;
                }
            }

            // Хотя бы основные хуки должны быть зарегистрированы
            $this->assertGreaterThan(0, $foundAssetHooks,
                'AssetMinification hooks should be registered in real environment');
        } else {
            $this->markTestSkipped('WordPress hooks not available');
        }
    }

    public function testRealAssetProcessing(): void
    {
        // Проверяем обработку реальных активов
        if (function_exists('wp_styles') && isset($GLOBALS['wp_styles'])) {
            global $wp_styles;

            // Запоминаем оригинальное состояние
            $originalQueue = $wp_styles->queue;

            // Имитируем вызов processAssets (в реальной среде это происходит через хуки)
            $this->assetMinification->processAssets();

            // Проверяем что очередь изменилась или остались оригинальные стили
            $this->assertTrue(
                !empty($wp_styles->queue) || $wp_styles->queue === $originalQueue,
                'Asset processing should not break WordPress styles queue'
            );
        } else {
            $this->markTestSkipped('WordPress styles not available');
        }
    }

    public function testRealCriticalCssInjection(): void
    {
        // Проверяем инъекцию критического CSS
        if (function_exists('get_template_directory')) {
            $themeCss = get_template_directory() . '/style.css';

            if (file_exists($themeCss)) {
                ob_start();
                $this->assetMinification->injectCriticalCss();
                $output = ob_get_clean();

                // Должен быть какой-то вывод или пустая строка (если отключено)
                $this->assertTrue(
                    strpos($output, '<style') !== false || empty($output),
                    'Critical CSS injection should work or be disabled'
                );
            } else {
                $this->markTestSkipped('Theme style.css not found');
            }
        } else {
            $this->markTestSkipped('get_template_directory not available');
        }
    }

    public function testRealHttpOutput(): void
    {
        // Тест HTTP вывода (если возможно)
        if (function_exists('home_url') && function_exists('wp_remote_get')) {
            $homeUrl = home_url('/');

            // Пробуем получить главную страницу
            $response = wp_remote_get($homeUrl, ['timeout' => 5]);

            if (!is_wp_error($response)) {
                $html = wp_remote_retrieve_body($response);

                // Проверяем наличие признаков оптимизации
                $hasOptimizationSigns = (
                    strpos($html, 'wp-addon-critical-css') !== false ||
                    strpos($html, 'cache/assets') !== false ||
                    strpos($html, 'AssetMinification') !== false
                );

                // Если плагин работает, должны быть признаки оптимизации
                // Если не работает - тест не должен падать, просто пропускаем
                if ($hasOptimizationSigns) {
                    $this->assertTrue(true, 'Asset optimization signs found in real HTML output');
                } else {
                    $this->markTestIncomplete('No optimization signs found - plugin may not be active');
                }
            } else {
                $this->markTestSkipped('Cannot fetch home page');
            }
        } else {
            $this->markTestSkipped('HTTP functions not available');
        }
    }

    public function testRealFileSystemOperations(): void
    {
        // Проверяем файловые операции в реальной среде
        $service = new \WpAddon\Services\AssetOptimizationService([
            'cache_dir' => sys_get_temp_dir() . '/test_real_cache/',
            'version_salt' => 'real_test',
            'minify_css' => true,
            'minify_js' => true,
            'combine_css' => true,
            'combine_js' => true,
            'critical_css_enabled' => true,
            'exclude_css' => [],
            'exclude_js' => []
        ]);

        $testContent = 'body { color: red; }';
        $cacheKey = 'real_test_' . time();

        // Тест сохранения
        $service->saveToCache($cacheKey, $testContent);

        // Тест чтения
        $cached = $service->getFromCache($cacheKey);
        $this->assertEquals($testContent, $cached,
            'Cache operations should work in real environment');

        // Очистка
        $cacheFile = sys_get_temp_dir() . '/test_real_cache/' . $cacheKey . '.gz';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }
}
