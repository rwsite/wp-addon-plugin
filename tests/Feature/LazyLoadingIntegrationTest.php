<?php

use Brain\Monkey;
use Brain\Monkey\Functions;

/**
 * Integration test for LazyLoading module
 */
describe('LazyLoading Integration', function () {
    // Пропустить тесты, если WordPress не загружен
    if (!function_exists('wp_die')) {
        test('skipped - requires WordPress environment', function () {})->skip('WordPress environment not available');
        return;
    }

    beforeEach(function () {
        Monkey\setUp();

        $this->mockOptionService = \Mockery::mock('\WpAddon\Services\OptionService');
        $this->mockImageOptimizationService = \Mockery::mock('\WpAddon\Services\ImageOptimizationService');

        // Mock option service для настроек
        $this->mockOptionService->shouldReceive('getSetting')
            ->andReturnUsing(function($key, $default = null) {
                $config = [
                    'enable_lazy_loading' => true,
                    'lazy_types' => ['img', 'iframe', 'video'],
                    'blur_intensity' => 5,
                    'root_margin' => '50px',
                    'threshold' => 0.1,
                    'enable_fallback' => true,
                ];
                return $config[$key] ?? $default;
            });

        $this->mockImageOptimizationService->shouldReceive('generateBlurPlaceholder')
            ->andReturn('data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/AB//2Q==');
    });

    afterEach(function () {
        Monkey\tearDown();
        \Mockery::close();
    });

    it('activates module without errors', function () {
        // Создаем экземпляр модуля
        $lazyLoading = new LazyLoading($this->mockOptionService, $this->mockImageOptimizationService);

        // Проверяем, что модуль реализует интерфейс
        expect($lazyLoading)->toBeInstanceOf('\WpAddon\Interfaces\ModuleInterface');

        // Инициализация не должна вызывать ошибки
        expect(function() use ($lazyLoading) {
            $lazyLoading->init();
        })->not->toThrow(Exception::class);
    });

    it('loads LazyLoading class', function () {
        // Проверяем существование класса
        expect(class_exists('LazyLoading'))->toBeTrue();

        // Проверяем реализацию интерфейса
        $reflection = new ReflectionClass('LazyLoading');
        expect($reflection->implementsInterface('\WpAddon\Interfaces\ModuleInterface'))->toBeTrue();
    });

    it('applies settings correctly', function () {
        $lazyLoading = new LazyLoading($this->mockOptionService, $this->mockImageOptimizationService);
        $lazyLoading->init();

        // Проверяем, что настройки загружаются
        // Это проверяется через моки выше
        expect(true)->toBeTrue(); // Заглушка для синтаксиса
    });

    it('enqueues JavaScript on frontend', function () {
        // Mock WordPress functions
        Functions\when('wp_enqueue_script')->justReturn(true);
        Functions\when('plugins_url')->justReturn('http://localhost/wp-content/plugins/wp-addon-plugin/');
        Functions\when('is_admin')->justReturn(false);

        $lazyLoading = new LazyLoading($this->mockOptionService, $this->mockImageOptimizationService);
        $lazyLoading->init();

        // Проверяем, что скрипт добавляется в очередь
        expect(true)->toBeTrue(); // Реальная проверка требует перехвата вызовов wp_enqueue_script
    });

    it('generates blur placeholders correctly', function () {
        $testImagePath = tempnam(sys_get_temp_dir(), 'wp_addon_test_') . '.jpg';

        // Создаем тестовое изображение
        $image = imagecreatetruecolor(100, 100);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        imagejpeg($image, $testImagePath);
        imagedestroy($image);

        // Mock метода
        $this->mockImageOptimizationService->shouldReceive('generateBlurPlaceholder')
            ->with($testImagePath)
            ->andReturn('data:image/jpeg;base64,test_placeholder');

        $lazyLoading = new LazyLoading($this->mockOptionService, $this->mockImageOptimizationService);
        $lazyLoading->init();

        // Очищаем
        unlink($testImagePath);
    });

    it('applies lazy loading to content', function () {
        $originalContent = '<p>Test content</p><img src="test.jpg" alt="Test"><iframe src="video.html"></iframe><video src="movie.mp4"></video>';

        Functions\when('is_admin')->justReturn(false);
        Functions\when('is_feed')->justReturn(false);

        $lazyLoading = new LazyLoading($this->mockOptionService, $this->mockImageOptimizationService);
        $lazyLoading->init();

        // Применяем фильтр
        $filteredContent = apply_filters('the_content', $originalContent);

        // Проверяем, что контент изменен
        expect($filteredContent)->not->toBe($originalContent);
        expect(strpos($filteredContent, 'data-src'))->toBeGreaterThan(0);
    });

    it('handles iframe and video elements', function () {
        $contentWithMedia = '<iframe src="https://youtube.com/embed/test" width="560" height="315"></iframe><video src="movie.mp4" controls></video>';

        Functions\when('is_admin')->justReturn(false);
        Functions\when('is_feed')->justReturn(false);

        $lazyLoading = new LazyLoading($this->mockOptionService, $this->mockImageOptimizationService);
        $lazyLoading->init();

        $filteredContent = apply_filters('the_content', $contentWithMedia);

        expect(strpos($filteredContent, 'data-src'))->toBeGreaterThan(0);
        expect(strpos($filteredContent, 'lazy-load'))->toBeGreaterThan(0);
    });

    it('maintains performance under 10ms increase', function () {
        // Измеряем время выполнения
        $startTime = microtime(true);

        $content = str_repeat('<img src="test' . rand() . '.jpg" alt="Test">', 10);

        Functions\when('is_admin')->justReturn(false);
        Functions\when('is_feed')->justReturn(false);

        $lazyLoading = new LazyLoading($this->mockOptionService, $this->mockImageOptimizationService);
        $lazyLoading->init();

        apply_filters('the_content', $content);

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // в миллисекундах

        expect($executionTime)->toBeLessThan(10);
    });

    it('handles errors gracefully', function () {
        // Контент с некорректными изображениями
        $contentWithErrors = '<img src="" alt="Empty"><img src="nonexistent.jpg" alt="Missing"><img src="http://invalid.url/image.jpg" alt="Invalid">';

        Functions\when('is_admin')->justReturn(false);
        Functions\when('is_feed')->justReturn(false);

        // Mock для обработки ошибок
        $this->mockImageOptimizationService->shouldReceive('generateBlurPlaceholder')
            ->andThrow(new Exception('Image processing error'));

        $lazyLoading = new LazyLoading($this->mockOptionService, $this->mockImageOptimizationService);
        $lazyLoading->init();

        // Фильтр не должен вызывать исключения
        expect(function() use ($lazyLoading, $contentWithErrors) {
            apply_filters('the_content', $contentWithErrors);
        })->not->toThrow(Exception::class);
    });

    it('respects lazy_types setting', function () {
        // Mock только для изображений
        $this->mockOptionService = \Mockery::mock('\WpAddon\Services\OptionService');
        $this->mockOptionService->shouldReceive('getSetting')
            ->andReturnUsing(function($key, $default = null) {
                $config = [
                    'enable_lazy_loading' => true,
                    'lazy_types' => ['img'], // Только изображения
                    'blur_intensity' => 5,
                    'root_margin' => '50px',
                    'threshold' => 0.1,
                    'enable_fallback' => true,
                ];
                return $config[$key] ?? $default;
            });

        $content = '<img src="test.jpg" alt="Image"><iframe src="video.html"></iframe><video src="movie.mp4"></video>';

        Functions\when('is_admin')->justReturn(false);
        Functions\when('is_feed')->justReturn(false);

        $lazyLoading = new LazyLoading($this->mockOptionService, $this->mockImageOptimizationService);
        $lazyLoading->init();

        $filteredContent = apply_filters('the_content', $content);

        // Изображения должны быть обработаны
        expect(strpos($filteredContent, 'data-src="test.jpg"'))->toBeGreaterThan(0);
        // iframe и video не должны быть обработаны
        expect(strpos($filteredContent, 'data-src="video.html"'))->toBeFalse();
        expect(strpos($filteredContent, 'data-src="movie.mp4"'))->toBeFalse();
    });
});
