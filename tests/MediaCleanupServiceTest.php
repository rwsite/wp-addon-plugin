<?php

use PHPUnit\Framework\TestCase;
use WpAddon\Services\MediaCleanupService;

class MediaCleanupServiceTest extends TestCase
{
    private MediaCleanupService $service;

    protected function setUp(): void
    {
        // Mock WP functions
        global $mock_options;
        $mock_options = [
            'thumbnail_size_w' => 150,
            'thumbnail_size_h' => 150,
            'medium_size_w' => 300,
            'medium_size_h' => 300,
            'large_size_w' => 1024,
            'large_size_h' => 1024,
        ];

        $this->service = new MediaCleanupService();
    }

    public function testGetRegisteredSizes()
    {
        $sizes = \WpAddon\Services\MediaCleanupService::getRegisteredSizesStatic();
        $this->assertIsArray($sizes);
        $this->assertContains('150x150', $sizes); // thumbnail default
        $this->assertContains('300x300', $sizes); // medium
        $this->assertContains('1024x1024', $sizes); // large
    }

    public function testIsFileToDelete()
    {
        $this->assertTrue($this->service->isFileToDelete('image-400x400.jpg'));
        $this->assertFalse($this->service->isFileToDelete('image-scaled.jpg'));
        $this->assertFalse($this->service->isFileToDelete('image.jpg')); // no size
        $this->assertFalse($this->service->isFileToDelete('image-150x150.jpg')); // registered
    }
}
