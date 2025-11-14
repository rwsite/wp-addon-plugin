<?php

use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\HookTrait;

class DisableComments implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        $this->addFilter('comments_open', '__return_false');
    }
}
