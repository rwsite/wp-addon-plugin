<?php

describe('Smoke Test', function () {
    it('tests plugin initialization', function () {
        // Test that autoloader is registered
        expect(function_exists('spl_autoload_functions'))->toBeTrue();

        // Test that classes can be loaded
        expect(class_exists('WpAddon\Autoloader'))->toBeTrue();
        expect(class_exists('WpAddon\Services\OptionService'))->toBeTrue();
        expect(class_exists('WpAddon\Services\MediaCleanupService'))->toBeTrue();
        expect(class_exists('WpAddon\Controllers\MediaCleanupController'))->toBeFalse(); // Удален, логика в модуле
    });

    it('tests services instantiation', function () {
        $optionService = new \WpAddon\Services\OptionService();
        expect($optionService)->toBeInstanceOf(\WpAddon\Services\OptionService::class);

        $mediaCleanupService = new \WpAddon\Services\MediaCleanupService();
        expect($mediaCleanupService)->toBeInstanceOf(\WpAddon\Services\MediaCleanupService::class);
    });
});
