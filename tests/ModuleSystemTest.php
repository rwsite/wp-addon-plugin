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

    public function testHookTraitMethods()
    {
        $mock = new class implements ModuleInterface {
            use HookTrait;
            public function init(): void {}
        };

        $this->assertTrue(method_exists($mock, 'addHook'));
        $this->assertTrue(method_exists($mock, 'addFilter'));
    }
}
