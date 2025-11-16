<?php

namespace WpAddon\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case for WP Addon Plugin tests
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Reset global WordPress objects
        global $wp_styles, $wp_scripts, $wp_inline_scripts, $mock_options;
        $wp_styles = null;
        $wp_scripts = null;
        $wp_inline_scripts = null;
        $mock_options = [];
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Get test data path
     */
    protected function getTestDataPath(string $filename = ''): string
    {
        $path = __DIR__ . '/data';
        return $filename ? $path . '/' . $filename : $path;
    }

    /**
     * Mock WordPress options
     */
    protected function mockOptions(array $options): void
    {
        global $mock_options;
        $mock_options = array_merge($mock_options, $options);
    }

    /**
     * Create temporary file with content
     */
    protected function createTempFile(string $content, string $extension = 'css'): string
    {
        $filename = tempnam(sys_get_temp_dir(), 'wp_addon_test_') . '.' . $extension;
        file_put_contents($filename, $content);
        return $filename;
    }

    /**
     * Remove temporary file
     */
    protected function removeTempFile(string $filename): void
    {
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * Mock WordPress styles queue
     */
    protected function mockWpStyles(array $styles): void
    {
        global $wp_styles;
        $wp_styles = new \stdClass();
        $wp_styles->queue = array_keys($styles);
        $wp_styles->registered = [];

        foreach ($styles as $handle => $style) {
            $wp_styles->registered[$handle] = (object) array_merge([
                'handle' => $handle,
                'src' => '',
                'deps' => [],
                'ver' => false,
                'args' => 'all',
            ], $style);
        }
    }

    /**
     * Mock WordPress scripts queue
     */
    protected function mockWpScripts(array $scripts): void
    {
        global $wp_scripts;
        $wp_scripts = new \stdClass();
        $wp_scripts->queue = array_keys($scripts);
        $wp_scripts->registered = [];

        foreach ($scripts as $handle => $script) {
            $wp_scripts->registered[$handle] = (object) array_merge([
                'handle' => $handle,
                'src' => '',
                'deps' => [],
                'ver' => false,
                'args' => false,
            ], $script);
        }
    }

    /**
     * Get mock asset optimization service
     */
    protected function getMockAssetOptimizationService(): \WpAddon\Services\AssetOptimizationService
    {
        $mock = $this->getMockBuilder(\WpAddon\Services\AssetOptimizationService::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Default behaviors
        $mock->method('minifyCss')->willReturnCallback(function($css) {
            return str_replace(['  ', "\n", "\t"], '', $css);
        });

        $mock->method('minifyJs')->willReturnCallback(function($js) {
            return str_replace(['  ', "\n", "\t"], '', $js);
        });

        $mock->method('combineCss')->willReturnCallback(function($files) {
            return implode("\n", array_map('file_get_contents', $files));
        });

        $mock->method('combineJs')->willReturnCallback(function($files) {
            return implode(";\n", array_map('file_get_contents', $files));
        });

        $mock->method('generateVersion')->willReturnCallback(function($content) {
            return md5($content);
        });

        return $mock;
    }

    /**
     * Call private method using reflection
     */
    protected function callPrivateMethod($object, string $methodName, array $args = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }
}
