# WP Addon Plugin

A comprehensive WordPress plugin designed to enhance site performance, security, SEO, and functionality through modular architecture and extensive customization options.

## Overview

WP Addon Plugin provides a suite of tools and optimizations for WordPress websites, enabling administrators and developers to improve site performance, implement security best practices, optimize SEO, and add custom functionality without extensive coding.

## Key Features

### Performance Optimization
- **Asset Minification**: Automatic CSS and JavaScript minification with caching (✅ Implemented)
- **Media Cleanup**: Remove unused image sizes to free up disk space
- **Database Optimization**: Fix GUID fields and optimize post revisions
- **Asset Management**: Disable unnecessary scripts (emojis, heartbeat, jQuery migrate)

### Security Enhancements
- Remove WordPress version from headers
- Disable XML-RPC and pingbacks
- Block aggressive updates
- Hide admin bar for non-admin users

### SEO & Content Management
- Automatic alt text for uploaded images
- Transliteration support
- Custom excerpt lengths
- Disable comments globally or selectively
- Post duplication functionality

### User Interface Improvements
- Enhanced TinyMCE editor with custom colors, fonts, and Bootstrap support
- Advanced dashboard widgets (server info, plugin list, user roles)
- Custom admin columns (post IDs, thumbnails)
- Maintenance mode

### Development Tools
- Custom shortcodes (FAQ, Table of Contents)
- Additional widgets (Yearly Archive)
- Custom CSS/JS/HTML injection
- **PerformanceTweaks Module**: Centralized performance optimizations
- Modular extension system

## Architecture

The plugin follows modern PHP development practices with:

- **PSR-4 Autoloading**: Organized namespace structure
- **Modular Design**: Interface-based module system for easy extension
- **Dependency Injection**: Clean service architecture
- **Comprehensive Testing**: PHPUnit test suite

### Directory Structure

```
wp-addon-plugin/
├── src/
│   ├── Interfaces/     # Module interfaces
│   ├── Traits/         # Reusable functionality traits
│   ├── Core/           # Main plugin initialization
│   ├── Services/       # Business logic services
│   ├── Config/         # Configuration arrays for settings
│   └── Controllers/    # Legacy controllers
├── functions/          # Module implementations
├── settings/           # Admin configuration
├── tests/              # Unit tests
├── assets/             # Static assets
└── languages/          # Translation files
```

## Installation

1. Download the plugin
2. Upload to `/wp-content/plugins/` directory
3. Activate through WordPress admin
4. Configure settings in **Settings > WP Addon**

### Requirements
- WordPress 5.0+
- PHP 7.4+
- Codestar Framework (for settings UI)

## Configuration

Access plugin settings through **WordPress Admin > Settings > WP Addon**. The plugin provides comprehensive configuration options across multiple sections:

- General Settings
- Posts & Pages
- Comments
- TinyMCE Editor
- SEO
- Database
- Dashboard
- Performance Tweaks
- Shortcodes & Widgets
- Custom Code

**Note:** Settings are stored in WordPress options under the key `'wp-addon'`. To access settings in code:

```php
$options = get_option('wp-addon', []);
$setting_value = $options['setting_id'] ?? default_value;
```

See [SETTINGS.md](SETTINGS.md) for detailed configuration guide.

## Module System

WP Addon features a flexible module system allowing easy extension and customization. Create new functionality by implementing the `ModuleInterface`.

### Creating Modules

1. Create a PHP file in `functions/YourModule.php`
2. Implement `WpAddon\Interfaces\ModuleInterface`
3. Use provided traits for common functionality

```php
<?php
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\HookTrait;

class CustomModule implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        $this->addFilter('the_content', [$this, 'modifyContent']);
    }

    public function modifyContent($content) {
        // Custom content modification
        return $content;
    }
}
```

See [MODULES_GUIDE.md](MODULES_GUIDE.md) for complete module development guide.

## Usage Examples

### Maintenance Mode
```php
<?php
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\HookTrait;

class MaintenanceMode implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        $options = get_option('wp-addon', []);
        if (!empty($options['enable_maintenance'])) {
            $this->addHook('template_redirect', [$this, 'checkMaintenance']);
        }
    }

    public function checkMaintenance() {
        if (!current_user_can('manage_options')) {
            // Show maintenance page
            wp_die('Site under maintenance');
        }
    }
}
```

### Disable Comments Site-wide
```php
<?php
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\HookTrait;

class DisableComments implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        $this->addFilter('comments_open', '__return_false');
    }
}
```

### Custom AJAX Endpoint
```php
<?php
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\AjaxTrait;
use WpAddon\Services\CustomService;

class AjaxHandler implements ModuleInterface {
    use AjaxTrait;

    private CustomService $service;

    public function __construct(CustomService $service) {
        $this->service = $service;
    }

    public function init(): void {
        $this->registerAjax('custom_action');
    }

    public function handleAjax(): void {
        // Handle AJAX request
        wp_send_json_success(['data' => 'response']);
    }
}
```

## Testing

Run the test suite:

```bash
cd wp-content/plugins/wp-addon-plugin
php phpunit.phar
```

## Development

- Follow PSR-12 coding standards
- Add PHPDoc for all public methods
- Write unit tests for new functionality
- Use the module system for new features

## Backward Compatibility

The plugin maintains compatibility with existing installations. Legacy functions are preserved while new features use the modular system.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## License

See LICENSE file for details.

## Support

For support and documentation:
- [Settings Guide](SETTINGS.md)
- [Module Development](MODULES_GUIDE.md)
- WordPress Plugin Directory

---

# Плагин WP Addon

Комплексный плагин для WordPress, предназначенный для улучшения производительности сайта, безопасности, SEO и функциональности через модульную архитектуру и широкие возможности настройки.

## Обзор

Плагин WP Addon предоставляет набор инструментов и оптимизаций для сайтов WordPress, позволяя администраторам и разработчикам улучшать производительность сайта, реализовывать лучшие практики безопасности, оптимизировать SEO и добавлять пользовательскую функциональность без обширного программирования.

## Основные возможности

### Оптимизация производительности
- **Очистка медиафайлов**: Удаление неиспользуемых размеров изображений для освобождения места на диске
- **Оптимизация базы данных**: Исправление полей GUID и оптимизация ревизий постов
- **Управление активами**: Отключение ненужных скриптов (эмодзи, heartbeat, jQuery migrate)

### Улучшения безопасности
- Удаление версии WordPress из заголовков
- Отключение XML-RPC и пингбеков
- Блокировка агрессивных обновлений
- Скрытие админ-бара для неадминистраторов

### SEO и управление контентом
- Автоматический alt-текст для загружаемых изображений
- Поддержка транслитерации
- Настраиваемые длины excerpt
- Глобальное или выборочное отключение комментариев
- Функциональность дублирования постов

### Улучшения интерфейса
- Расширенный редактор TinyMCE с пользовательскими цветами, шрифтами и поддержкой Bootstrap
- Продвинутые виджеты дашборда (информация о сервере, список плагинов, роли пользователей)
- Пользовательские колонки админки (ID постов, миниатюры)
- Режим обслуживания

### Инструменты разработки
- Пользовательские шорткоды (FAQ, Оглавление)
- Дополнительные виджеты (Годовой архив)
- Внедрение пользовательского CSS/JS/HTML
- **Модуль PerformanceTweaks**: Централизованные оптимизации производительности
- Модульная система расширений

## Архитектура

Плагин следует современным практикам разработки PHP:

- **Автозагрузка PSR-4**: Организованная структура пространств имен
- **Модульный дизайн**: Интерфейсная модульная система для легкого расширения
- **Внедрение зависимостей**: Чистая архитектура сервисов
- **Комплексное тестирование**: Набор тестов PHPUnit

### Структура директорий

```
wp-addon-plugin/
├── src/
│   ├── Interfaces/     # Интерфейсы модулей
│   ├── Traits/         # Переиспользуемые трейты функциональности
│   ├── Core/           # Основная инициализация плагина
│   ├── Services/       # Сервисы бизнес-логики
│   ├── Config/         # Массивы конфигурации настроек
│   └── Controllers/    # Устаревшие контроллеры
├── functions/          # Реализации модулей
├── settings/           # Конфигурация админки
├── tests/              # Модульные тесты
├── assets/             # Статические ресурсы
└── languages/          # Файлы переводов
```

## Установка

1. Скачайте плагин
2. Загрузите в директорию `/wp-content/plugins/`
3. Активируйте через админку WordPress
4. Настройте параметры в **Настройки > WP Addon**

### Требования
- WordPress 5.0+
- PHP 7.4+
- Codestar Framework (для UI настроек)

## Конфигурация

Доступ к настройкам плагина через **WordPress Admin > Настройки > WP Addon**. Плагин предоставляет комплексные опции конфигурации в нескольких разделах:

- Общие настройки
- Посты и страницы
- Комментарии
- Редактор TinyMCE
- SEO
- База данных
- Дашборд
- Твики производительности
- Шорткоды и виджеты
- Пользовательский код

**Примечание:** Настройки сохраняются в опциях WordPress под ключом `'wp-addon'`. Для доступа к настройкам в коде:

```php
$options = get_option('wp-addon', []);
$value = $options['setting_id'] ?? default_value;
```

Смотрите [SETTINGS.md](SETTINGS.md) для подробного руководства по настройке.

## Модульная система

WP Addon обладает гибкой модульной системой, позволяющей легкое расширение и кастомизацию. Создавайте новую функциональность, реализуя `ModuleInterface`.

### Создание модулей

1. Создайте PHP файл в `functions/YourModule.php`
2. Реализуйте `WpAddon\Interfaces\ModuleInterface`
3. Используйте предоставленные трейты для общей функциональности

```php
<?php
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\HookTrait;

class CustomModule implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        $this->addFilter('the_content', [$this, 'modifyContent']);
    }

    public function modifyContent($content) {
        // Кастомная модификация контента
        return $content;
    }
}
```

Смотрите [MODULES_GUIDE.md](MODULES_GUIDE.md) для полного руководства по разработке модулей.

## Примеры использования

### Режим обслуживания
```php
<?php
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\HookTrait;

class MaintenanceMode implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        $options = get_option('wp-addon', []);
        if (!empty($options['enable_maintenance'])) {
            $this->addHook('template_redirect', [$this, 'checkMaintenance']);
        }
    }

    public function checkMaintenance() {
        if (!current_user_can('manage_options')) {
            // Показать страницу обслуживания
            wp_die('Сайт на техническом обслуживании');
        }
    }
}
```

### Отключение комментариев на всем сайте
```php
<?php
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\HookTrait;

class DisableComments implements ModuleInterface {
    use HookTrait;

    public function init(): void {
        $this->addFilter('comments_open', '__return_false');
    }
}
```

### Пользовательская AJAX конечная точка
```php
<?php
use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\AjaxTrait;
use WpAddon\Services\CustomService;

class AjaxHandler implements ModuleInterface {
    use AjaxTrait;

    private CustomService $service;

    public function __construct(CustomService $service) {
        $this->service = $service;
    }

    public function init(): void {
        $this->registerAjax('custom_action');
    }

    public function handleAjax(): void {
        // Обработка AJAX запроса
        wp_send_json_success(['data' => 'response']);
    }
}
```

## Тестирование

Запустите набор тестов:

```bash
cd wp-content/plugins/wp-addon-plugin
php phpunit.phar
```

## Разработка

- Следуйте стандартам кодирования PSR-12
- Добавляйте PHPDoc для всех публичных методов
- Пишите модульные тесты для новой функциональности
- Используйте модульную систему для новых функций

## Обратная совместимость

Плагин поддерживает совместимость с существующими установками. Устаревшие функции сохранены, в то время как новые возможности используют модульную систему.

## Вклад в проект

1. Форкните репозиторий
2. Создайте ветку для функции
3. Добавьте тесты для новой функциональности
4. Убедитесь, что все тесты проходят
5. Отправьте pull request

## Лицензия

Смотрите файл LICENSE для деталей.

## Поддержка

Для поддержки и документации:
- [Руководство по настройкам](SETTINGS.md)
- [Разработка модулей](MODULES_GUIDE.md)
- Директория плагинов WordPress