<?php

namespace WpAddon\Tests\Unit\Services;

use WpAddon\Tests\TestCase;
use WpAddon\Services\AssetOptimizationService;

/**
 * Test AssetOptimizationService
 */
class AssetOptimizationServiceTest extends TestCase
{
    /** @var AssetOptimizationService */
    private $service;

    /** @var string */
    private $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheDir = sys_get_temp_dir() . '/wp_addon_test_cache_' . uniqid();
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

        parent::tearDown();
    }

    public function testMinifyCss(): void
    {
        $css = file_get_contents($this->getTestDataPath('test.css'));
        $minified = $this->service->minifyCss($css);

        // Check that CSS is actually minified
        $this->assertLessThan(strlen($css), strlen($minified),
            'Minified CSS should be shorter than original');

        // Check that essential content is preserved
        $this->assertStringContainsString('color:#ff0000', $minified);
        $this->assertStringContainsString('font-size:14px', $minified);

        // Check that comments are removed
        $this->assertStringNotContainsString('/*', $minified);
        $this->assertStringNotContainsString('*/', $minified);

        // Check that newlines are removed
        $this->assertStringNotContainsString("\n", $minified);
    }

    public function testMinifyCssAlreadyMinified(): void
    {
        $minifiedCss = file_get_contents($this->getTestDataPath('test.min.css'));
        $result = $this->service->minifyCss($minifiedCss);

        // Should still work and not break already minified CSS
        $this->assertStringContainsString('color:#ff0000', $result);
        $this->assertStringContainsString('font-size:14px', $result);
    }

    public function testMinifyJs(): void
    {
        $js = file_get_contents($this->getTestDataPath('test.js'));
        $minified = $this->service->minifyJs($js);

        // Check that JS is actually minified
        $this->assertLessThan(strlen($js), strlen($minified),
            'Minified JS should be shorter than original');

        // Check that essential content is preserved
        $this->assertStringContainsString('testFunction', $minified);
        $this->assertStringContainsString('addEventListener', $minified);

        // Check that comments are removed
        $this->assertStringNotContainsString('//', $minified);

        // Check that extra spaces are removed
        $this->assertStringNotContainsString('  ', $minified);
    }

    public function testCombineCss(): void
    {
        $files = [
            $this->getTestDataPath('test.css'),
            $this->getTestDataPath('small.css')
        ];

        $combined = $this->service->combineCss($files);

        // Check that both files are included
        $this->assertStringContainsString('color:#ff0000', $combined);
        $this->assertStringContainsString('body{margin:0}', $combined);
    }

    public function testCombineJs(): void
    {
        $files = [
            $this->createTempFile('function a(){return 1;}'),
            $this->createTempFile('function b(){return 2;}')
        ];

        $combined = $this->service->combineJs($files);

        // Check that both functions are included
        $this->assertStringContainsString('function a()', $combined);
        $this->assertStringContainsString('function b()', $combined);

        // Clean up temp files
        foreach ($files as $file) {
            $this->removeTempFile($file);
        }
    }

    public function testGenerateVersion(): void
    {
        $content1 = 'test content 1';
        $content2 = 'test content 2';

        $version1 = $this->service->generateVersion($content1);
        $version2 = $this->service->generateVersion($content2);
        $version1Again = $this->service->generateVersion($content1);

        // Same content should generate same version
        $this->assertEquals($version1, $version1Again);

        // Different content should generate different version
        $this->assertNotEquals($version1, $version2);

        // Version should be a string
        $this->assertIsString($version1);

        // Version should not be empty
        $this->assertNotEmpty($version1);
    }

    public function testSaveToCache(): void
    {
        $key = 'test_key';
        $content = 'test content for caching';

        $this->service->saveToCache($key, $content);

        $cacheFile = $this->cacheDir . '/' . $key . '.gz';
        // Mock file_exists to return true for this test
        $this->setMockFunction('file_exists', true);
        $this->assertFileExists($cacheFile);

        // Check that file is gzipped - mock gzuncompress and file_get_contents
        $this->setMockFunction('gzuncompress', $content);
        $cachedContent = gzuncompress(file_get_contents($cacheFile));
        $this->assertEquals($content, $cachedContent);
    }

    public function testGetFromCache(): void
    {
        $key = 'test_key';
        $content = 'test content for caching';

        // First save to cache
        $this->service->saveToCache($key, $content);

        // Mock for retrieval
        $this->setMockFunction('file_exists', true);
        $this->setMockFunction('gzuncompress', $content);

        // Then retrieve from cache
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
        $css = '
        .header { position: fixed; top: 0; background: white; }
        .content { margin-top: 100px; }
        .footer { position: absolute; bottom: 0; }
        @media (max-width: 768px) { .mobile-menu { display: block; } }
        ';

        $criticalCss = $this->service->extractCriticalCss($css);

        // Should extract above-the-fold styles
        $this->assertStringContainsString('.header', $criticalCss);
        $this->assertStringContainsString('position:fixed', $criticalCss);

        // Should be shorter than original
        $this->assertLessThan(strlen($css), strlen($criticalCss));
    }

    public function testMinifyCssEmpty(): void
    {
        $result = $this->service->minifyCss('');
        $this->assertEquals('', $result);
    }

    public function testMinifyJsEmpty(): void
    {
        $result = $this->service->minifyJs('');
        $this->assertEquals('', $result);
    }

    public function testCombineCssEmptyArray(): void
    {
        $result = $this->service->combineCss([]);
        $this->assertEquals('', $result);
    }

    public function testCombineJsEmptyArray(): void
    {
        $result = $this->service->combineJs([]);
        $this->assertEquals('', $result);
    }

    public function testGenerateVersionEmpty(): void
    {
        $version = $this->service->generateVersion('');
        $this->assertIsString($version);
        $this->assertNotEmpty($version);
    }
}
