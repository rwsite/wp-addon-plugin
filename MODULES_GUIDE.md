# Module Development Guide

This guide explains how to create and manage modules in the WP Addon Plugin using the modular architecture system.

## Overview

The WP Addon Plugin uses a flexible module system that allows developers to extend functionality without modifying core plugin files. Modules are automatically discovered and initialized by the plugin's core system.

## Module Basics

### What is a Module?

A module is a self-contained piece of functionality that implements the `ModuleInterface`. Modules can:

- Register WordPress hooks and filters
- Handle AJAX requests
- Create custom widgets
- Provide new shortcodes
- Modify existing functionality

### Module Interface

All modules must implement `WpAddon\Interfaces\ModuleInterface`:

```php
<?php
namespace WpAddon\Interfaces;

interface ModuleInterface {
    public function init(): void;
}
```

The `init()` method is called automatically when the plugin loads, allowing your module to register hooks, filters, and other functionality.

## Creating a Module

### Step 1: Create the Module File

Create a new PHP file in the `functions/` directory. The filename should match your class name:

```
functions/MyCustomModule.php
```

### Step 2: Implement the Interface

```php
<?php
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\HookTrait;

class MyCustomModule implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        // Module initialization code here
        $this->addAction('init', [$this, 'onInit']);
    }

    public function onInit(): void {
        // Custom functionality
    }
}
```

### Step 3: Use Available Traits

The plugin provides several traits to simplify common WordPress development tasks:

#### HookTrait

Use for registering actions and filters:

```php
<?php
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\HookTrait;

class ContentModifier implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        $this->addFilter('the_content', [$this, 'modifyContent'], 10, 1);
        $this->addAction('wp_head', [$this, 'addMetaTags']);
    }

    public function modifyContent($content) {
        // Modify post content
        return $content . '<p>Modified by module</p>';
    }

    public function addMetaTags() {
        echo '<meta name="custom" content="value" />';
    }
}
```

#### AjaxTrait

Use for handling AJAX requests:

```php
<?php
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\AjaxTrait;

class AjaxHandler implements ModuleInterface {
    use AjaxTrait;

    public function init(): void {
        $this->registerAjax('my_custom_action');
    }

    public function handleAjax(): void {
        // Handle AJAX request
        $data = $_POST['data'] ?? '';

        if (current_user_can('manage_options')) {
            wp_send_json_success(['result' => 'Success', 'data' => $data]);
        } else {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
    }
}
```

#### WidgetTrait

Use for creating custom widgets:

```php
<?php
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\WidgetTrait;

class MyWidget extends WP_Widget implements ModuleInterface {
    use WidgetTrait;

    public function __construct() {
        parent::__construct(
            'my_widget',
            __('My Custom Widget', 'wp-addon'),
            ['description' => __('A custom widget example', 'wp-addon')]
        );
    }

    public function init(): void {
        $this->registerWidget();
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo $args['before_title'] . __('My Widget', 'wp-addon') . $args['after_title'];
        echo '<p>Hello from custom widget!</p>';
        echo $args['after_widget'];
    }
}
```

## Advanced Module Features

### Dependency Injection

Modules can receive services through constructor injection:

```php
<?php
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\AjaxTrait;
use WpAddon\Services\MediaCleanupService;

class AdvancedAjaxModule implements ModuleInterface {
    use AjaxTrait;

    private MediaCleanupService $mediaService;

    public function __construct(MediaCleanupService $mediaService) {
        $this->mediaService = $mediaService;
    }

    public function init(): void {
        $this->registerAjax('advanced_cleanup');
    }

    public function handleAjax(): void {
        $result = $this->mediaService->getFilesToDelete(wp_upload_dir()['basedir']);
        wp_send_json_success($result);
    }
}
```

**Note:** Only specific services are automatically injected. For custom services, you may need to modify the core loading logic.

### Module Configuration

Modules can check plugin settings:

```php
<?php
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\HookTrait;

class ConfigurableModule implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        $enabled = get_option('wp_addon_custom_feature', false);
        if ($enabled) {
            $this->addFilter('the_content', [$this, 'modifyContent']);
        }
    }

    public function modifyContent($content) {
        // Modify content based on configuration
        return $content;
    }
}
```

## Module Loading Process

1. Plugin scans `functions/*.php` and `functions/*/*.php`
2. Files are included via `require_once`
3. Classes implementing `ModuleInterface` are detected
4. For known classes (like `MediaCleanup`), services are injected
5. `init()` method is called on each module instance

## Best Practices

### Naming Conventions

- Use PascalCase for class names
- Match filename to class name
- Use descriptive names: `UserProfileEnhancer`, `SecurityHardener`

### Error Handling

```php
<?php
class SafeModule implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        try {
            $this->addAction('init', [$this, 'riskyOperation']);
        } catch (Exception $e) {
            error_log('Module initialization failed: ' . $e->getMessage());
        }
    }

    public function riskyOperation() {
        // Risky code with error handling
    }
}
```

### Performance Considerations

- Avoid heavy operations in `init()`
- Use lazy loading where possible
- Cache expensive operations

### Security

- Always validate user input
- Check capabilities before performing actions
- Use nonces for AJAX requests

## Example Modules

### SEO Enhancer

```php
<?php
class SeoEnhancer implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        $this->addFilter('wpseo_metadesc', [$this, 'enhanceMetaDescription']);
        $this->addAction('wp_head', [$this, 'addStructuredData']);
    }

    public function enhanceMetaDescription($desc) {
        if (empty($desc)) {
            return get_the_excerpt() ?: get_bloginfo('description');
        }
        return $desc;
    }

    public function addStructuredData() {
        if (is_single()) {
            // Add JSON-LD structured data
            echo '<script type="application/ld+json">' . json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => get_the_title(),
                'author' => get_the_author(),
            ]) . '</script>';
        }
    }
}

### Maintenance Mode

```php
<?php
class MaintenanceMode implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        $maintenance = get_option('wp_addon_maintenance_mode', false);
        if ($maintenance && !current_user_can('manage_options')) {
            $this->addAction('template_redirect', [$this, 'showMaintenancePage']);
        }
    }

    public function showMaintenancePage() {
        include plugin_dir_path(__FILE__) . '../templates/maintenance.php';
        exit;
    }
}

### Performance Tweaks Module

```php
<?php
class PerformanceTweaks implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        // Check plugin settings and apply optimizations
        if (get_option('wptweaker_setting_2', true)) {
            $this->addAction('init', [$this, 'disableEmojis']);
        }
        if (get_option('wptweaker_setting_10', false)) {
            $this->addAction('init', [$this, 'disableHeartbeat']);
        }
        // Add more tweaks based on settings
    }

    public function disableEmojis() {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
    }

    public function disableHeartbeat() {
        wp_deregister_script('heartbeat');
    }
}

### Maintenance Mode

```php
<?php
class MaintenanceMode implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        if (get_option('enable_maintenance', false)) {
            $this->addAction('get_header', [$this, 'showMaintenancePage']);
        }
    }

    public function showMaintenancePage() {
        if (!current_user_can('edit_themes')) {
            wp_die(__('Site under maintenance', 'wp-addon'));
        }
    }
}

## Testing Modules

Create unit tests for your modules in the `tests/` directory:

```php
<?php
use PHPUnit\Framework\TestCase;

class MyModuleTest extends TestCase {
    public function testModuleInitialization() {
        $module = new MyCustomModule();
        $this->assertInstanceOf('WpAddon\Interfaces\ModuleInterface', $module);
    }

    public function testModuleFunctionality() {
        // Test specific functionality
    }
}
```

## Troubleshooting

### Module Not Loading

- Ensure class name matches filename
- Verify `ModuleInterface` is properly implemented
- Check for PHP syntax errors
- Review error logs

### Hooks Not Working

- Verify hook names and priorities
- Check if hooks are registered in `init()`
- Ensure proper callback signatures

### AJAX Not Responding

- Confirm AJAX action is registered
- Check user capabilities
- Verify nonce validation
- Test with different user roles

## Migration from Legacy Code

If migrating existing functionality to modules:

1. Extract logic into module class
2. Implement `ModuleInterface`
3. Replace direct hook registrations with trait methods
4. Remove old code after testing

This modular approach ensures clean, maintainable, and extensible WordPress plugin development.
