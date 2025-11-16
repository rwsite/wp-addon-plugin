<?php

use Brain\Monkey;
use Brain\Monkey\Functions;
use \Mockery;

/**
 * Unit tests for LazyLoading module
 */
describe('LazyLoading Unit Tests', function () {
    beforeEach(function () {
        Monkey\setUp();

        $this->mockOptionService = Mockery::mock('\WpAddon\Services\OptionService');

        // Настройки по умолчанию для новой версии
        $this->mockOptionService->shouldReceive('getSetting')
            ->andReturnUsing(function($key, $default = null) {
                $config = [
                    'enable_lazy_loading' => true,
                ];
                return $config[$key] ?? $default;
            });
    });

    afterEach(function () {
        Monkey\tearDown();
        Mockery::close();
    });

    it('initializes and registers hooks when enabled', function () {
        Functions\when('add_filter')->justReturn(true);
        Functions\when('add_action')->justReturn(true);
        Functions\when('wp_enqueue_script')->justReturn(true);

        $lazyLoading = new LazyLoading($this->mockOptionService);
        $lazyLoading->init();

        expect(true)->toBeTrue();
    });

    it('does not initialize when disabled', function () {
        $this->mockOptionService->shouldReceive('getSetting')
            ->with('enable_lazy_loading', false)
            ->andReturn(false);

        $lazyLoading = new LazyLoading($this->mockOptionService);
        $lazyLoading->init();

        expect(true)->toBeTrue();
    });

    it('applies lazy loading to HTML content with images', function () {
        $lazyLoading = new LazyLoading($this->mockOptionService);

        $html = '<p>Some text</p><img src="/wp-content/uploads/image.jpg" alt="Test" class="wp-image"><p>More text</p>';
        $result = $lazyLoading->processContent($html);

        expect($result)->toContain('data-src="/wp-content/uploads/image.jpg"');
        expect($result)->toContain('class="wp-image lazy-img"');
        expect($result)->toContain('loading="lazy"');
        expect($result)->not()->toContain('src="/wp-content/uploads/image.jpg"');
    });

    it('skips SVG images', function () {
        $lazyLoading = new LazyLoading($this->mockOptionService);

        $html = '<img src="/wp-content/uploads/image.svg" alt="SVG">';
        $result = $lazyLoading->processContent($html);

        expect($result)->toContain('src="/wp-content/uploads/image.svg"');
        expect($result)->not()->toContain('data-src');
    });

    it('skips data URL images', function () {
        $lazyLoading = new LazyLoading($this->mockOptionService);

        $html = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==" alt="Data">';
        $result = $lazyLoading->processContent($html);

        expect($result)->toContain('src="data:image/png;base64,');
        expect($result)->not()->toContain('data-src');
    });

    it('skips images with no-lazy class', function () {
        $lazyLoading = new LazyLoading($this->mockOptionService);

        $html = '<img src="/wp-content/uploads/image.jpg" alt="Test" class="no-lazy">';
        $result = $lazyLoading->processContent($html);

        expect($result)->toContain('src="/wp-content/uploads/image.jpg"');
        expect($result)->not()->toContain('data-src');
    });

    it('handles multiple images in content', function () {
        $lazyLoading = new LazyLoading($this->mockOptionService);

        $html = '<img src="/uploads/1.jpg" alt="1"><img src="/uploads/2.jpg" alt="2"><img src="/uploads/3.svg" alt="3">';
        $result = $lazyLoading->processContent($html);

        expect($result)->toContain('data-src="/uploads/1.jpg"');
        expect($result)->toContain('data-src="/uploads/2.jpg"');
        expect($result)->toContain('src="/uploads/3.svg"'); // SVG пропускается
        expect($result)->not()->toContain('src="/uploads/1.jpg"');
        expect($result)->not()->toContain('src="/uploads/2.jpg"');
    });

    it('preserves existing attributes', function () {
        $lazyLoading = new LazyLoading($this->mockOptionService);

        $html = '<img src="/image.jpg" alt="Test" width="100" height="100" class="existing-class" id="test-img">';
        $result = $lazyLoading->processContent($html);

        expect($result)->toContain('data-src="/image.jpg"');
        expect($result)->toContain('alt="Test"');
        expect($result)->toContain('width="100"');
        expect($result)->toContain('height="100"');
        expect($result)->toContain('class="existing-class lazy-img"');
        expect($result)->toContain('id="test-img"');
    });
});
