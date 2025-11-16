<?php

use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\HookTrait;
use WpAddon\Services\OptionService;

/**
 * Simple Lazy Loading module for images
 */
class LazyLoading implements ModuleInterface
{
    use HookTrait;

    private OptionService $optionService;
    private bool $enabled;

    public function __construct(OptionService $optionService)
    {
        $this->optionService = $optionService;
        $this->enabled = $this->optionService->getSetting('enable_lazy_loading', false);
    }

    public function init(): void
    {
        if (!$this->enabled || is_admin()) {
            return;
        }

        $this->addHook('the_content', [$this, 'processContent']);
        $this->addHook('wp_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    public function processContent(string $content): string
    {
        return preg_replace_callback(
            '/<img([^>]+)>/i',
            [$this, 'processImage'],
            $content
        );
    }

    private function processImage(array $matches): string
    {
        $img = $matches[0];
        $attrs = $this->parseAttributes($matches[1]);

        // Skip if no src
        if (empty($attrs['src'])) {
            return $img;
        }

        $src = $attrs['src'];

        // Skip SVG, data URLs, and no-lazy images
        if ($this->shouldSkip($src, $attrs)) {
            return $img;
        }

        // Build lazy attributes
        $newAttrs = $attrs;
        $newAttrs['data-src'] = $src;
        unset($newAttrs['src']);
        $newAttrs['class'] = ($attrs['class'] ?? '') . ' lazy-img';
        $newAttrs['loading'] = 'lazy';

        return '<img' . $this->buildAttributes($newAttrs) . '>';
    }

    private function shouldSkip(string $src, array $attrs): bool
    {
        return strpos($src, '.svg') !== false
            || strpos($src, 'data:') === 0
            || (isset($attrs['class']) && strpos($attrs['class'], 'no-lazy') !== false);
    }

    private function parseAttributes(string $attrString): array
    {
        $attrs = [];
        preg_match_all('/(\w+)="([^"]*)"/', $attrString, $matches);
        foreach ($matches[1] as $i => $name) {
            $attrs[$name] = $matches[2][$i];
        }
        return $attrs;
    }

    private function buildAttributes(array $attrs): string
    {
        $parts = [];
        foreach ($attrs as $name => $value) {
            $parts[] = $name . '="' . htmlspecialchars($value) . '"';
        }
        return ' ' . implode(' ', $parts);
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_script(
            'lazy-loading',
            RW_PLUGIN_URL . 'assets/js/lazy-loading.js',
            [],
            '1.0.0',
            true
        );
    }
}
