# Руководство по разработке модулей для WP Addon Plugin

## Для AI-ассистента Windsurf

Это руководство описывает процесс разработки новых модулей для плагина WP Addon Plugin с использованием методологии Test-Driven Development (TDD). Следуя этому подходу, мы гарантируем высокое качество кода, надежность и отсутствие багов.

## Основные принципы

1. **TDD подход**: Сначала тесты, потом код
2. **Чекпоинты работоспособности**: Определяем критерии успеха заранее
3. **Интеграционные тесты**: Проверяют работу модуля целиком
4. **Unit тесты**: Проверяют отдельные компоненты
5. **Чистый код**: Удаляем все временные файлы перед коммитом

## Шаги разработки нового модуля

### Шаг 1: Анализ требований и определение чекпоинтов

**Что делать:**
- Определить функциональные требования модуля
- Выделить ключевые чекпоинты работоспособности
- Определить входные данные и ожидаемые результаты

**Пример чекпоинтов для нового модуля:**
```markdown
- ✅ Модуль активируется без ошибок
- ✅ Класс модуля загружается и инициализируется
- ✅ Конфигурация загружается правильно
- ✅ Основная функция выполняется корректно
- ✅ Обработка ошибок работает
- ✅ Производительность не ухудшается
```

**Код:**
```php
// Определить чекпоинты в комментариях
// TODO: Определить чекпоинты работоспособности
```

### Шаг 2: Создание интеграционных тестов

**Что делать:**
- Создать файл `tests/integration/ModuleNameTest.php`
- Написать тесты, проверяющие чекпоинты
- Использовать реальные сервисы и моки WordPress функций
- Тесты должны падать до реализации функционала

**Структура интеграционного теста:**
```php
<?php
namespace WpAddon\Tests\Integration;

use WpAddon\Tests\TestCase;
use Brain\Monkey;

class ModuleNameTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        // Настройка моков
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testModuleIsActivated(): void
    {
        // Чекпоинт 1: Модуль активирован
        // Проверка наличия модуля в системе
    }

    public function testModuleLoads(): void
    {
        // Чекпоинт 2: Класс загружается
        // Проверка существования класса и интерфейса
    }

    public function testMainFunctionality(): void
    {
        // Чекпоинт 3: Основная функция работает
        // Тест основного функционала
    }
}
```

### Шаг 3: Создание unit тестов

**Что делать:**
- Создать файл `tests/unit/ModuleNameTest.php`
- Протестировать отдельные методы
- Мокировать зависимости
- Проверить edge cases

**Пример unit теста:**
```php
<?php
namespace WpAddon\Tests\Unit;

use WpAddon\Tests\TestCase;
use Brain\Monkey;
use Mockery;

class ModuleNameTest extends TestCase
{
    private $module;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        $this->service = Mockery::mock('ServiceClass');
        $this->module = new ModuleName($this->service);
    }

    public function testProcessData(): void
    {
        // Arrange
        $input = 'test data';
        $expected = 'processed data';

        $this->service->shouldReceive('process')
            ->with($input)
            ->andReturn($expected);

        // Act
        $result = $this->module->processData($input);

        // Assert
        $this->assertEquals($expected, $result);
    }
}
```

### Шаг 4: Реализация функционала

**Что делать:**
- Создать класс модуля в `functions/ModuleName.php`
- Реализовать интерфейс `ModuleInterface`
- Использовать trait `HookTrait` для хуков
- Добавить проверки и обработку ошибок

**Структура модуля:**
```php
<?php

use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\HookTrait;

class ModuleName implements ModuleInterface
{
    use HookTrait;

    private $optionService;

    public function __construct($optionService)
    {
        $this->optionService = $optionService;
    }

    public function init(): void
    {
        // Регистрация хуков
        $this->addHook('hook_name', [$this, 'callback'], 10);

        // Проверка настроек
        if (!$this->optionService->getSetting('module_enabled')) {
            return;
        }

        // Инициализация логики
    }

    public function callback(): void
    {
        // Основная логика
        try {
            // Реализация
        } catch (Exception $e) {
            error_log('ModuleName error: ' . $e->getMessage());
        }
    }
}
```

### Шаг 5: Регистрация модуля в системе

**Что делать:**
- Добавить модуль в `src/Core/Plugin.php` в методе `loadModules()`
- Проверить, что модуль загружается при активации плагина

**Код в Plugin.php:**
```php
private function loadModules(): void
{
    // Существующие модули...

    // Новый модуль
    if ($className === 'ModuleName') {
        $module = new $className($this->optionService);
    }
}
```

### Шаг 6: Тестирование и отладка

**Что делать:**
- Запускать тесты: `./run-tests.sh`
- Проверять покрытие кода
- Исправлять ошибки
- Добавлять отладочные логи при необходимости

**Команды:**
```bash
# Запуск всех тестов
./run-tests.sh

# Запуск интеграционных тестов
./run-tests.sh integration

# Запуск unit тестов
./run-tests.sh unit
```

### Шаг 7: Очистка и документирование

**Что делать:**
- Удалить временные файлы и отладочные логи
- Обновить документацию (README.md, SETTINGS.md)
- Добавить модуль в список функций

**Чек-лист готовности:**
- [ ] Все тесты проходят
- [ ] Код соответствует PSR-12
- [ ] Документация обновлена
- [ ] Временные файлы удалены
- [ ] Производительность проверена

## Пример: Разработка модуля AssetMinification

### Определение чекпоинтов
- ✅ Плагин активирован
- ✅ Класс AssetMinification существует
- ✅ Конфигурация загружается
- ✅ CSS минифицируется и кэшируется
- ✅ JS минифицируется и кэшируется
- ✅ Critical CSS извлекается
- ✅ Хуки зарегистрированы

### Интеграционные тесты
Создан `AssetMinificationRealWorldTest.php` с проверкой всех чекпоинтов.

### Unit тесты
Создан `AssetMinificationTest.php` с тестированием создания кэш-файлов.

### Реализация
- Исправлен код `AssetMinification.php`
- Добавлена минификация и кэширование
- Регистрация хуков на `wp_print_styles` и `wp_print_scripts`

### Результат
Модуль работает в продакшене, создает минифицированные файлы с GZip сжатием.

## Важные замечания

1. **Всегда проверять синтаксис**: `php -l filename.php`
2. **Использовать type hints**: Для параметров и возвращаемых значений
3. **Обработка ошибок**: Логировать исключения, не прерывать работу сайта
4. **Производительность**: Избегать тяжелых операций в хуках
5. **Безопасность**: Валидировать входные данные
6. **Совместимость**: Проверять с разными версиями WordPress/PHP

## Инструменты

- **PHPUnit**: Для запуска тестов
- **Brain\Monkey**: Для мокинга WordPress функций
- **Mockery**: Для мокинга сервисов
- **PHPStan/PHPCS**: Для проверки кода

## Контакты

При разработке новых модулей следуйте этому руководству для обеспечения качества и надежности кода.
