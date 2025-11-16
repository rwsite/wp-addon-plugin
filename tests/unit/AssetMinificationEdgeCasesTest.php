<?php

namespace WpAddon\Tests\Unit;

use WpAddon\Tests\TestCase;
use AssetMinification;

/**
 * Test AssetMinification edge cases and error handling
 */
class AssetMinificationEdgeCasesTest extends TestCase
{
    /** @var AssetMinification */
    private $assetMinification;

    /** @var \WpAddon\Services\OptionService */
    private $mockOptionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockOptionService = $this->createMock(\WpAddon\Services\OptionService::class);

        // Mock option service with default config
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
                    'exclude_css' => [],
                    'exclude_js' => [],
                    'cache_dir' => sys_get_temp_dir() . '/wp_addon_cache',
                    'version_salt' => 'wp-addon-v1',
                ];
                return $config[$key] ?? $default;
            });

        $this->assetMinification = new AssetMinification($this->mockOptionService);
    }

    public function testProcessAssetsWithEmptyQueue(): void
    {
        // Setup: Empty queues
        $this->mockWpStyles([]);
        $this->mockWpScripts([]);

        // Execute - should not throw any errors
        $this->assetMinification->processAssets();

        // Assert: Queues should remain empty
        global $wp_styles, $wp_scripts;
        $this->assertEmpty($wp_styles->queue);
        $this->assertEmpty($wp_scripts->queue);
    }

    public function testProcessAssetsWithNullWpStyles(): void
    {
        // Setup: Null wp_styles
        global $wp_styles, $wp_scripts;
        $wp_styles = null;
        $wp_scripts = [];

        // Execute - should not throw any errors
        $this->assetMinification->processAssets();

        // Assert: Nothing should break
        $this->assertNull($wp_styles);
    }

    public function testProcessAssetsWithNullWpScripts(): void
    {
        // Setup: Null wp_scripts
        global $wp_styles, $wp_scripts;
        $wp_styles = [];
        $wp_scripts = null;

        // Execute - should not throw any errors
        $this->assetMinification->processAssets();

        // Assert: Nothing should break
        $this->assertNull($wp_scripts);
    }

    public function testShouldProcessAssetWithEmptySrc(): void
    {
        $result = $this->callPrivateMethod($this->assetMinification, 'shouldProcessAsset', ['test-handle', '', []]);
        $this->assertFalse($result, 'Assets with empty src should not be processed');
    }

    public function testShouldProcessAssetWithNullSrc(): void
    {
        $result = $this->callPrivateMethod($this->assetMinification, 'shouldProcessAsset', ['test-handle', null, []]);
        $this->assertFalse($result, 'Assets with null src should not be processed');
    }

    public function testShouldProcessAssetWithInvalidPath(): void
    {
        // Mock file_exists to return false for invalid path
        $this->setMockFunction('file_exists', false);

        $result = $this->callPrivateMethod($this->assetMinification, 'shouldProcessAsset', [
            'test-handle',
            'http://example.com/wp-content/themes/theme/invalid.css',
            []
        ]);
        $this->assertFalse($result, 'Assets with invalid file paths should not be processed');
    }

    public function testIsAlreadyMinifiedWithMalformedCss(): void
    {
        $malformedCss = '.class { color: #fff; font-size: 14px; /* unclosed comment ';
        $result = $this->callPrivateMethod($this->assetMinification, 'isAlreadyMinified', [$malformedCss, 'css']);

        // Should not crash and return a boolean
        $this->assertIsBool($result);
    }

    public function testIsAlreadyMinifiedWithMalformedJs(): void
    {
        $malformedJs = 'function test() { console.log("test"); /* unclosed comment ';
        $result = $this->callPrivateMethod($this->assetMinification, 'isAlreadyMinified', [$malformedJs, 'js']);

        // Should not crash and return a boolean
        $this->assertIsBool($result);
    }

    public function testUrlToPathWithEmptyUrl(): void
    {
        $result = $this->assetMinification->urlToPath('');
        $this->assertEquals('', $result);
    }

    public function testUrlToPathWithNonMatchingUrl(): void
    {
        $url = 'https://cdn.example.com/style.css';
        $result = $this->assetMinification->urlToPath($url);

        // Should still work but return path relative to ABSPATH
        $this->assertIsString($result);
        $this->assertStringContainsString('https://cdn.example.com/style.css', $result);
    }

    public function testGetCacheUrlWithEmptyKey(): void
    {
        $result = $this->assetMinification->getCacheUrl('');
        $this->assertStringContainsString('.gz', $result);
        $this->assertStringContainsString('cache/assets/', $result);
    }

    public function testGetCacheUrlWithSpecialCharacters(): void
    {
        $key = 'test@key#with$special%chars';
        $result = $this->assetMinification->getCacheUrl($key);

        $this->assertStringContainsString($key, $result);
        $this->assertStringContainsString('.gz', $result);
    }

    public function testInjectCriticalCssWithMissingThemeCss(): void
    {
        // Mock get_template_directory to return non-existent path
        $this->setMockFunction('get_template_directory', '/non/existent/theme/path');

        // Execute - should not throw errors
        ob_start();
        $this->assetMinification->injectCriticalCss();
        $output = ob_get_clean();

        // Assert: No critical CSS should be injected
        $this->assertEmpty($output);
    }

    public function testInjectCriticalCssWithEmptyThemeCss(): void
    {
        // Create empty theme CSS file
        $emptyCssFile = $this->createTempFile('', 'css');
        $themeDir = dirname($emptyCssFile);

        $this->setMockFunction('get_template_directory', $themeDir);

        // Execute
        ob_start();
        $this->assetMinification->injectCriticalCss();
        $output = ob_get_clean();

        // Assert: No critical CSS should be injected for empty file
        $this->assertEmpty($output);

        // Clean up
        $this->removeTempFile($emptyCssFile);
    }

    public function testInjectCriticalCssWithCorruptThemeCss(): void
    {
        // Create corrupt CSS file
        $corruptCss = 'this is not css {{{ }}} }}}';
        $corruptCssFile = $this->createTempFile($corruptCss, 'css');
        $themeDir = dirname($corruptCssFile);

        $this->setMockFunction('get_template_directory', $themeDir);

        // Execute - should not throw errors
        ob_start();
        $this->assetMinification->injectCriticalCss();
        $output = ob_get_clean();

        // Assert: Should handle corrupt CSS gracefully
        $this->assertIsString($output);

        // Clean up
        $this->removeTempFile($corruptCssFile);
    }

    public function testDeferCssLoadingWithNoCombinedJs(): void
    {
        // Mock wp_add_inline_script to capture calls
        $inlineScripts = [];
        // Setup inline scripts tracking
        global $wp_inline_scripts;
        $wp_inline_scripts = [];

        // Execute
        $this->assetMinification->deferCssLoading();

        // Assert: Script should still be added even without combined JS
        $this->assertArrayHasKey('wp-addon-combined-js', $wp_inline_scripts);
        $this->assertStringContainsString('DOMContentLoaded', $wp_inline_scripts['wp-addon-combined-js']);
    }

    public function testClearCacheWithNonExistentDirectory(): void
    {
        // Mock cache directory that doesn't exist
        $nonExistentDir = '/tmp/non_existent_wp_addon_cache_' . uniqid();

        $this->mockOptionService = $this->createMock(\WpAddon\Services\OptionService::class);
        $this->mockOptionService->method('getSetting')
            ->willReturnCallback(function($key) use ($nonExistentDir) {
                return $key === 'cache_dir' ? $nonExistentDir : true;
            });

        $assetMinification = new AssetMinification($this->mockOptionService);

        // Execute - should not throw errors
        $assetMinification->clearCache();

        // Assert: Nothing should break
        $this->assertTrue(true);
    }

    public function testClearCacheWithPermissionDenied(): void
    {
        // Create cache directory and make it read-only
        $cacheDir = sys_get_temp_dir() . '/wp_addon_readonly_cache_' . uniqid();
        mkdir($cacheDir, 0444, true); // Read-only

        $this->mockOptionService = $this->createMock(\WpAddon\Services\OptionService::class);
        $this->mockOptionService->method('getSetting')
            ->willReturnCallback(function($key) use ($cacheDir) {
                return $key === 'cache_dir' ? $cacheDir : true;
            });

        $assetMinification = new AssetMinification($this->mockOptionService);

        // Execute - should handle permission errors gracefully
        $assetMinification->clearCache();

        // Assert: Should not throw fatal errors
        $this->assertTrue(true);

        // Clean up (change permissions back to allow deletion)
        chmod($cacheDir, 0755);
        rmdir($cacheDir);
    }

    public function testProcessAssetsInAdminArea(): void
    {
        // Mock is_admin() to return true
        $this->setMockFunction('is_admin', true);

        // Setup some assets
        $this->mockWpStyles(['test-style' => ['src' => 'http://example.com/style.css']]);
        $this->mockWpScripts(['test-script' => ['src' => 'http://example.com/script.js']]);

        // Execute
        $this->assetMinification->processAssets();

        // Assert: Assets should not be processed in admin area
        global $wp_styles, $wp_scripts;
        $this->assertContains('test-style', $wp_styles->queue);
        $this->assertContains('test-script', $wp_scripts->queue);
    }

    public function testProcessAssetsDuringAjax(): void
    {
        // Mock wp_doing_ajax() to return true
        $this->setMockFunction('wp_doing_ajax', true);

        // Setup some assets
        $this->mockWpStyles(['test-style' => ['src' => 'http://example.com/style.css']]);
        $this->mockWpScripts(['test-script' => ['src' => 'http://example.com/script.js']]);

        // Execute
        $this->assetMinification->processAssets();

        // Assert: Assets should not be processed during AJAX
        global $wp_styles, $wp_scripts;
        $this->assertContains('test-style', $wp_styles->queue);
        $this->assertContains('test-script', $wp_scripts->queue);
    }

    public function testGetAssetPriorityWithUnknownAsset(): void
    {
        $priority = $this->callPrivateMethod($this->assetMinification, 'getAssetPriority', ['unknown-asset-handle']);
        $this->assertEquals(2, $priority, 'Unknown assets should get normal priority (2)');
    }

    public function testGetAssetPriorityWithEmptyHandle(): void
    {
        $priority = $this->callPrivateMethod($this->assetMinification, 'getAssetPriority', ['']);
        $this->assertEquals(2, $priority, 'Empty handles should get normal priority (2)');
    }

    public function testIsSystemAssetWithEmptyHandle(): void
    {
        $result = $this->callPrivateMethod($this->assetMinification, 'isSystemAsset', ['']);
        $this->assertFalse($result, 'Empty handles should not be considered system assets');
    }

    public function testIsSystemAssetWithNullHandle(): void
    {
        $result = $this->callPrivateMethod($this->assetMinification, 'isSystemAsset', [null]);
        $this->assertFalse($result, 'Null handles should not be considered system assets');
    }
}
