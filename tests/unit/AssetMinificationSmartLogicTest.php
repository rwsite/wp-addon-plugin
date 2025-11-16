<?php

namespace WpAddon\Tests\Unit;

use WpAddon\Tests\TestCase;
use AssetMinification;

/**
 * Test AssetMinification smart logic
 */
class AssetMinificationSmartLogicTest extends TestCase
{
    /** @var AssetMinification */
    private $assetMinification;

    /** @var \WpAddon\Services\AssetOptimizationService */
    private $mockService;

    /** @var \WpAddon\Services\OptionService */
    private $mockOptionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockService = $this->getMockAssetOptimizationService();
        $this->mockOptionService = $this->createMock(\WpAddon\Services\OptionService::class);

        // Mock option service to return default config
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
                    'cache_dir' => sys_get_temp_dir() . '/wp_addon_cache',
                    'version_salt' => 'wp-addon-v1',
                ];
                return $config[$key] ?? $default;
            });

        $this->assetMinification = new AssetMinification($this->mockOptionService);
    }

    public function testShouldProcessAssetSystemAssetsExcluded(): void
    {
        // Test WordPress core assets that should be excluded
        $systemAssets = [
            'jquery',
            'jquery-core',
            'jquery-migrate',
            'admin-bar',
            'dashicons',
            'thickbox',
            'heartbeat',
            'wp-embed',
            'wp-emoji',
        ];

        foreach ($systemAssets as $asset) {
            $this->assertFalse(
                $this->callPrivateMethod($this->assetMinification, 'shouldProcessAsset', [$asset, 'http://example.com/wp-includes/js/jquery.js', []]),
                "System asset '{$asset}' should be excluded"
            );
        }
    }

    public function testShouldProcessAssetExplicitlyExcluded(): void
    {
        $excludes = ['custom-plugin-css', 'theme-style'];

        $this->assertFalse(
            $this->callPrivateMethod($this->assetMinification, 'shouldProcessAsset', ['custom-plugin-css', 'http://example.com/wp-content/plugins/plugin/style.css', $excludes]),
            'Explicitly excluded asset should not be processed'
        );

        $this->assertFalse(
            $this->callPrivateMethod($this->assetMinification, 'shouldProcessAsset', ['theme-style', 'http://example.com/wp-content/themes/theme/style.css', $excludes]),
            'Explicitly excluded asset should not be processed'
        );
    }

    public function testShouldProcessAssetExternalUrlsExcluded(): void
    {
        // External URLs should be excluded
        $externalUrls = [
            'https://cdn.jsdelivr.net/jquery.js',
            'https://fonts.googleapis.com/css?family=Roboto',
            'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js',
        ];

        foreach ($externalUrls as $url) {
            $this->assertFalse(
                $this->callPrivateMethod($this->assetMinification, 'shouldProcessAsset', ['external-asset', $url, []]),
                "External URL '{$url}' should be excluded"
            );
        }
    }

    public function testShouldProcessAssetLocalUrlsIncluded(): void
    {
        // Local URLs should be included
        $localUrls = [
            'http://example.com/wp-content/themes/theme/style.css',
            'http://example.com/wp-content/plugins/plugin/script.js',
            '/wp-content/uploads/custom.css',
        ];

        foreach ($localUrls as $url) {
            $this->assertTrue(
                $this->callPrivateMethod($this->assetMinification, 'shouldProcessAsset', ['local-asset', $url, []]),
                "Local URL '{$url}' should be included"
            );
        }
    }

    public function testShouldProcessAssetSmallFilesExcluded(): void
    {
        // Mock filesize to return small size
        $this->setMockFunction('filesize', 500); // 500 bytes

        $this->assertFalse(
            $this->callPrivateMethod($this->assetMinification, 'shouldProcessAsset', ['small-file', 'http://example.com/wp-content/themes/theme/small.css', []]),
            'Files smaller than 1KB should be excluded'
        );
    }

    public function testShouldProcessAssetLargeFilesIncluded(): void
    {
        // Mock filesize to return large size
        $this->setMockFunction('filesize', 2048); // 2KB

        $this->assertTrue(
            $this->callPrivateMethod($this->assetMinification, 'shouldProcessAsset', ['large-file', 'http://example.com/wp-content/themes/theme/large.css', []]),
            'Files larger than 1KB should be included'
        );
    }

    public function testShouldProcessAssetNonexistentFilesExcluded(): void
    {
        // Mock file_exists to return false
        $this->setMockFunction('file_exists', false);

        $this->assertFalse(
            $this->callPrivateMethod($this->assetMinification, 'shouldProcessAsset', ['missing-file', 'http://example.com/wp-content/themes/theme/missing.css', []]),
            'Nonexistent files should be excluded'
        );
    }

    public function testShouldProcessAssetValidFilesIncluded(): void
    {
        // Mock file system functions
        $this->setMockFunction('file_exists', true);
        $this->setMockFunction('filesize', 2048);

        $this->assertTrue(
            $this->callPrivateMethod($this->assetMinification, 'shouldProcessAsset', ['valid-file', 'http://example.com/wp-content/themes/theme/valid.css', []]),
            'Valid local files should be included'
        );
    }

    public function testIsAlreadyMinifiedCss(): void
    {
        // Test minified CSS
        $minifiedCss = '.class{color:#fff;font-size:14px}.another{margin:0}';
        $this->assertTrue(
            $this->callPrivateMethod($this->assetMinification, 'isAlreadyMinified', [$minifiedCss, 'css']),
            'Minified CSS should be detected as already minified'
        );

        // Test unminified CSS
        $unminifiedCss = ".class {\n    color: #fff;\n    font-size: 14px;\n}\n\n.another {\n    margin: 0;\n}";
        $this->assertFalse(
            $this->callPrivateMethod($this->assetMinification, 'isAlreadyMinified', [$unminifiedCss, 'css']),
            'Unminified CSS should not be detected as already minified'
        );
    }

    public function testIsAlreadyMinifiedJs(): void
    {
        // Test minified JS
        $minifiedJs = 'function test(){var a=1;return a*2}document.addEventListener("load",test)';
        $this->assertTrue(
            $this->callPrivateMethod($this->assetMinification, 'isAlreadyMinified', [$minifiedJs, 'js']),
            'Minified JS should be detected as already minified'
        );

        // Test unminified JS
        $unminifiedJs = "function test() {\n    var a = 1;\n    return a * 2;\n}\n\ndocument.addEventListener('load', test);";
        $this->assertFalse(
            $this->callPrivateMethod($this->assetMinification, 'isAlreadyMinified', [$unminifiedJs, 'js']),
            'Unminified JS should not be detected as already minified'
        );
    }

    public function testIsAlreadyMinifiedEdgeCases(): void
    {
        // Test empty content
        $this->assertFalse($this->callPrivateMethod($this->assetMinification, 'isAlreadyMinified', ['', 'css']));
        $this->assertFalse($this->callPrivateMethod($this->assetMinification, 'isAlreadyMinified', ['', 'js']));

        // Test invalid type
        $this->assertFalse($this->callPrivateMethod($this->assetMinification, 'isAlreadyMinified', ['content', 'invalid']));
    }

    public function testIsSystemAsset(): void
    {
        // Test known system assets
        $systemAssets = [
            'jquery', 'jquery-core', 'jquery-migrate', 'jquery-ui',
            'admin-bar', 'dashicons', 'heartbeat', 'wp-embed'
        ];

        foreach ($systemAssets as $asset) {
            $this->assertTrue(
                $this->callPrivateMethod($this->assetMinification, 'isSystemAsset', [$asset]),
                "'{$asset}' should be recognized as system asset"
            );
        }

        // Test non-system assets
        $nonSystemAssets = [
            'custom-script', 'theme-style', 'plugin-css', 'bootstrap'
        ];

        foreach ($nonSystemAssets as $asset) {
            $this->assertFalse(
                $this->callPrivateMethod($this->assetMinification, 'isSystemAsset', [$asset]),
                "'{$asset}' should not be recognized as system asset"
            );
        }
    }

    public function testGetAssetPriority(): void
    {
        // Test critical priority
        $criticalAssets = ['theme-styles', 'style', 'main-style', 'custom-css'];
        foreach ($criticalAssets as $asset) {
            $this->assertEquals(0, $this->callPrivateMethod($this->assetMinification, 'getAssetPriority', [$asset]));
        }

        // Test high priority
        $highAssets = ['bootstrap', 'foundation', 'font-awesome', 'icons'];
        foreach ($highAssets as $asset) {
            $this->assertEquals(1, $this->callPrivateMethod($this->assetMinification, 'getAssetPriority', [$asset]));
        }

        // Test normal priority (default)
        $normalAssets = ['custom-script', 'theme-js', 'plugin-css'];
        foreach ($normalAssets as $asset) {
            $this->assertEquals(2, $this->callPrivateMethod($this->assetMinification, 'getAssetPriority', [$asset]));
        }

        // Test low priority
        $lowAssets = ['social-share', 'comments', 'related-posts'];
        foreach ($lowAssets as $asset) {
            $this->assertEquals(3, $this->callPrivateMethod($this->assetMinification, 'getAssetPriority', [$asset]));
        }
    }

    public function testUrlToPath(): void
    {
        $url = 'http://example.com/wp-content/themes/theme/style.css';
        $expectedPath = '/var/www/html/wp-content/themes/theme/style.css';

        $this->assertEquals($expectedPath, $this->callPrivateMethod($this->assetMinification, 'urlToPath', [$url]));
    }

    public function testGetCacheUrl(): void
    {
        $key = 'test-cache-key';
        $expectedUrl = 'http://example.com/wp-content/cache/assets/test-cache-key.gz';

        $this->assertEquals($expectedUrl, $this->callPrivateMethod($this->assetMinification, 'getCacheUrl', [$key]));
    }
}
