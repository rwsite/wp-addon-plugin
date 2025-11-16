<?php

use PHPUnit\Framework\TestCase;
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\AjaxTrait;
use WpAddon\Traits\WidgetTrait;
use WpAddon\Traits\HookTrait;

class ModuleSystemTest extends TestCase
{
    public function testModuleInterfaceExists()
    {
        $this->assertTrue(interface_exists('WpAddon\Interfaces\ModuleInterface'));
    }

    public function testTraitsExist()
    {
        $this->assertTrue(trait_exists('WpAddon\Traits\AjaxTrait'));
        $this->assertTrue(trait_exists('WpAddon\Traits\WidgetTrait'));
        $this->assertTrue(trait_exists('WpAddon\Traits\HookTrait'));
    }

    public function testAjaxTraitMethods()
    {
        $mock = new class implements ModuleInterface {
            use AjaxTrait;
            public function init(): void {}
            public function handleAjax(): void {}
        };

        $this->assertTrue(method_exists($mock, 'registerAjax'));
    }

    public function testWidgetTraitMethods()
    {
        $mock = new class implements ModuleInterface {
            use WidgetTrait;
            public function init(): void {}
            public function widget($args, $instance): void {}
        };

        $this->assertTrue(method_exists($mock, 'registerWidget'));
    }

    public function testRedirectsModule()
    {
        // Load the Redirects class file
        require_once __DIR__ . '/../functions/Redirects.php';

        // Test that Redirects class exists and implements ModuleInterface
        $this->assertTrue(class_exists('Redirects'));
        $this->assertTrue(is_subclass_of('Redirects', 'WpAddon\Interfaces\ModuleInterface'));

        // Test instantiation
        $redirects = new Redirects();
        $this->assertInstanceOf(ModuleInterface::class, $redirects);

        // Test that init method exists
        $this->assertTrue(method_exists($redirects, 'init'));
    }
}
