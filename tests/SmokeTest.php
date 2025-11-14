<?php

use PHPUnit\Framework\TestCase;

class SmokeTest extends TestCase
{
    public function testPluginInitialization()
    {
        // Test that autoloader is registered
        $this->assertTrue(function_exists('spl_autoload_functions'));

        // Test that classes can be loaded
        $this->assertTrue(class_exists('WpAddon\Autoloader'));
        $this->assertTrue(class_exists('WpAddon\Services\OptionService'));
        $this->assertTrue(class_exists('WpAddon\Services\MediaCleanupService'));
        $this->assertFalse(class_exists('WpAddon\Controllers\MediaCleanupController')); // Удален, логика в модуле
    }

    public function testServicesInstantiation()
    {
        $optionService = new \WpAddon\Services\OptionService();
        $this->assertInstanceOf(\WpAddon\Services\OptionService::class, $optionService);

        $mediaCleanupService = new \WpAddon\Services\MediaCleanupService();
        $this->assertInstanceOf(\WpAddon\Services\MediaCleanupService::class, $mediaCleanupService);
    }
}
