<?php

namespace WpAddon\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use WpAddon\Services\AssetOptimizationService;

/**
 * Simple test AssetOptimizationService without mocks
 */
class SimpleAssetOptimizationServiceTest extends TestCase
{
    /** @var AssetOptimizationService */
    private $service;

    /** @var string */
    private $cacheDir;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/wp_addon_simple_test_cache_' . uniqid();
        mkdir($this->cacheDir, 0755, true);

        $config = [
            'cache_dir' => $this->cacheDir,
            'version_salt' => 'test-salt',
            'minify_css' => true,
            'minify_js' => true,
            'combine_css' => true,
            'combine_js' => true,
            'critical_css_enabled' => true,
            'exclude_css' => [],
            'exclude_js' => []
        ];

        $this->service = new AssetOptimizationService($config);
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
    }

    public function testMinifyCssBasic(): void
    {
        $css = "
        .test-class {
            color: #ff0000;
            font-size: 14px;
        }
        .another { margin: 0; }
        ";

        $minified = $this->service->minifyCss($css);

        // Check that CSS is minified
        $this->assertFalse(strpos($minified, "\n"));
        $this->assertFalse(strpos($minified, "  "));
        $this->assertTrue(strpos($minified, '.test-class{color:#ff0000;font-size:14px}.another{margin:0}') !== false);
    }

    public function testMinifyCssRemovesComments(): void
    {
        $css = "/* This is a comment */ .test { color: red; } /* Another comment */";
        $minified = $this->service->minifyCss($css);

        $this->assertFalse(strpos($minified, '/*'));
        $this->assertFalse(strpos($minified, '*/'));
        $this->assertTrue(strpos($minified, '.test{color:red}') !== false);
    }

    public function testMinifyJsBasic(): void
    {
        $js = "
        function test() {
            var a = 1;
            return a + 2;
        }
        ";

        $minified = $this->service->minifyJs($js);

        // Check that JS is minified
        $this->assertFalse(strpos($minified, "\n"));
        $this->assertFalse(strpos($minified, "  "));
        $this->assertTrue(strpos($minified, 'function test(){var a=1;return a+2;}') !== false);
    }

    public function testMinifyJsRemovesComments(): void
    {
        $js = "/* Block comment */ function test() { return 1; } // Line comment";
        $minified = $this->service->minifyJs($js);

        $this->assertFalse(strpos($minified, '/*'));
        $this->assertFalse(strpos($minified, '*/'));
        $this->assertFalse(strpos($minified, '//'));
        $this->assertTrue(strpos($minified, 'function test(){return 1;}') !== false);
    }

    public function testGenerateVersion(): void
    {
        $content1 = 'test content';
        $content2 = 'different content';

        $version1 = $this->service->generateVersion($content1);
        $version2 = $this->service->generateVersion($content2);

        $this->assertEquals(8, strlen($version1)); // Should be 8 characters
        $this->assertIsString($version1);
        $this->assertNotEquals($version1, $version2); // Different content = different version
    }

    public function testGenerateVersionSameContent(): void
    {
        $content = 'test content';

        $version1 = $this->service->generateVersion($content);
        $version2 = $this->service->generateVersion($content);

        $this->assertEquals($version1, $version2); // Same content = same version
    }

    public function testSaveAndGetFromCache(): void
    {
        $key = 'test_key';
        $content = 'test content for caching';

        // Save to cache
        $this->service->saveToCache($key, $content);

        // Get from cache
        $cachedContent = $this->service->getFromCache($key);

        $this->assertEquals($content, $cachedContent);
    }

    public function testGetFromCacheNonexistent(): void
    {
        $cachedContent = $this->service->getFromCache('nonexistent_key');
        $this->assertNull($cachedContent);
    }

    public function testExtractCriticalCss(): void
    {
        $css = "
        .header { position: fixed; background: white; }
        .content { margin-top: 100px; }
        .footer { position: absolute; bottom: 0; }
        .nav { display: block; }
        ";

        $criticalCss = $this->service->extractCriticalCss($css);

        // Should extract header, nav and footer as critical
        $this->assertTrue(strpos($criticalCss, '.header') !== false);
        $this->assertTrue(strpos($criticalCss, '.nav') !== false);
        $this->assertTrue(strpos($criticalCss, '.footer') !== false);

        // Should not contain content
        $this->assertFalse(strpos($criticalCss, '.content'));
    }

    public function testCombineCssWithFiles(): void
    {
        // Create temporary CSS files
        $css1 = $this->createTempFile('.class1 { color: red; }');
        $css2 = $this->createTempFile('.class2 { color: blue; }');

        $combined = $this->service->combineCss([$css1, $css2]);

        // Should contain both classes
        $this->assertTrue(strpos($combined, '.class1{color:red}') !== false);
        $this->assertTrue(strpos($combined, '.class2{color:blue}') !== false);

        // Clean up
        unlink($css1);
        unlink($css2);
    }

    public function testCombineJsWithFiles(): void
    {
        // Create temporary JS files
        $js1 = $this->createTempFile('function a() { return 1; }');
        $js2 = $this->createTempFile('function b() { return 2; }');

        $combined = $this->service->combineJs([$js1, $js2]);

        // Should contain both functions
        $this->assertTrue(strpos($combined, 'function a(){return 1;};') !== false);
        $this->assertTrue(strpos($combined, 'function b(){return 2;};') !== false);

        // Clean up
        unlink($js1);
        unlink($js2);
    }

    private function createTempFile($content): string
    {
        $filename = tempnam(sys_get_temp_dir(), 'wp_addon_test_');
        file_put_contents($filename, $content);
        return $filename;
    }
}
