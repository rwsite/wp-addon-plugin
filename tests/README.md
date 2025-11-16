# Руководство по тестированию WP Addon Plugin

Это руководство описывает систему тестирования для WP Addon Plugin, включая принципы TDD (Test-Driven Development), лучшие практики написания тестов и пошаговое руководство по разработке новых модулей.

## 📋 Обзор тестирования

WP Addon Plugin использует комплексную систему тестирования с:
- **Unit тестами** для изоляции компонентов
- **Feature тестами** для интеграции модулей
- **Smoke тестами** для проверки базовой функциональности
- **Полным покрытием** критических путей кода

### 🎯 Цели тестирования

- Гарантия работоспособности всех модулей
- Предотвращение регрессий при изменениях
- Документация поведения через тесты
- Поддержка рефакторинга без страха поломок

## 🏗️ Структура тестовой среды

```
tests/
├── bootstrap.php              # Настройка тестового окружения
├── TestCase.php              # Базовый класс с утилитами
├── DatabaseMigrations.php    # Трейт для работы с БД
├── Unit/                     # Unit тесты компонентов
│   ├── LazyLoadingTest.php
│   ├── FactoriesTest.php
│   ├── ModuleSystemTest.php
│   └── [модуль]Test.php
├── Feature/                  # Feature тесты интеграции
│   ├── LazyLoadingIntegrationTest.php
│   ├── SmokeTest.php
│   └── ExampleTest.php
└── Factories/                # Фабрики для тестовых данных
    ├── PostFactory.php
    ├── AssetFactory.php
    └── [Factory].php
```

## 🚀 Быстрый старт

### Установка зависимостей

```bash
composer install
```

### Запуск всех тестов

```bash
composer test
```

### Запуск с покрытием

```bash
composer test:coverage
# Результаты: coverage/index.html
```

## 📝 Разработка через тестирование (TDD)

### 🔄 TDD цикл

```
🔴 RED → 🟢 GREEN → 🔵 REFACTOR
```

1. **RED**: Напиши тест, который падает
2. **GREEN**: Реализуй минимальный код для прохождения
3. **REFACTOR**: Улучши код, сохраняя тесты зелёными

### 📋 Шаги разработки нового модуля

#### 1. Определи требования модуля

```php
// Пример: Новый модуль ImageOptimization
// Требования:
// - Оптимизация изображений при загрузке
// - Поддержка форматов JPG, PNG, WebP
// - Сохранение качества > 80%
// - Логирование процесса
```

#### 2. Создай интерфейс модуля

```php
// src/Interfaces/ImageOptimizationInterface.php
interface ImageOptimizationInterface extends ModuleInterface
{
    public function optimizeImage(string $filePath): bool;
    public function getSupportedFormats(): array;
}
```

#### 3. Напиши тесты ПЕРЕД реализацией

```php
// tests/Unit/ImageOptimizationTest.php
describe('ImageOptimization', function () {
    beforeEach(function () {
        $this->service = new ImageOptimizationService();
    });

    it('optimizes JPG images', function () {
        $originalSize = filesize('/path/to/test.jpg');
        $result = $this->service->optimizeImage('/path/to/test.jpg');

        expect($result)->toBeTrue();
        expect(filesize('/path/to/test.jpg'))->toBeLessThan($originalSize);
    });

    it('returns supported formats', function () {
        $formats = $this->service->getSupportedFormats();

        expect($formats)->toContain('jpg');
        expect($formats)->toContain('png');
        expect($formats)->toContain('webp');
    });

    it('preserves image quality', function () {
        // Тест на качество после оптимизации
        $originalQuality = $this->getImageQuality('/path/to/test.jpg');
        $this->service->optimizeImage('/path/to/test.jpg');
        $optimizedQuality = $this->getImageQuality('/path/to/test.jpg');

        expect($optimizedQuality)->toBeGreaterThan(80);
    });
});
```

#### 4. Реализуй минимальный код

```php
// src/Services/ImageOptimizationService.php
class ImageOptimizationService
{
    public function optimizeImage(string $filePath): bool
    {
        // Минимальная реализация для прохождения тестов
        if (!file_exists($filePath)) {
            return false;
        }

        // Заглушка - просто возвращаем true
        return true;
    }

    public function getSupportedFormats(): array
    {
        return ['jpg', 'png', 'webp'];
    }
}
```

#### 5. Запусти тесты и исправь

```bash
composer test tests/Unit/ImageOptimizationTest.php
```

#### 6. Добавь интеграционные тесты

```php
// tests/Feature/ImageOptimizationIntegrationTest.php
describe('ImageOptimization Integration', function () {
    it('integrates with WordPress upload', function () {
        // Тест полной интеграции с WP
        $attachmentId = $this->createTestImage();
        $optimized = apply_filters('wp_handle_upload', ['file' => '/path/to/image.jpg']);

        expect($optimized)->toBeTrue();
        expect($this->isImageOptimized($attachmentId))->toBeTrue();
    });
});
```

#### 7. Добавь фабрики для тестовых данных

```php
// tests/Factories/ImageFactory.php
class ImageFactory extends Factory
{
    protected function getModelClass(): string
    {
        return 'attachment';
    }

    protected function define(): array
    {
        return [
            'post_title' => $this->faker->sentence(),
            'post_type' => 'attachment',
            'post_mime_type' => 'image/jpeg',
            // ... другие поля
        ];
    }
}
```

## 🧪 Типы тестов

### Unit тесты

Тестируют отдельные компоненты в изоляции:

```php
describe('OptionService', function () {
    it('retrieves option values', function () {
        $service = new OptionService();
        $value = $service->getSetting('test_option', 'default');

        expect($value)->toBe('default');
    });
});
```

### Feature тесты

Тестируют интеграцию компонентов:

```php
describe('LazyLoading Integration', function () {
    it('applies lazy loading to content', function () {
        $module = new LazyLoading($optionService);
        $content = '<img src="image.jpg" alt="test">';

        $result = $module->processContent($content);

        expect($result)->toContain('loading="lazy"');
    });
});
```

### Smoke тесты

Проверяют базовую работоспособность:

```php
describe('Smoke Test', function () {
    it('plugin initializes without errors', function () {
        $plugin = new WpAddonPlugin();
        $result = $plugin->init();

        expect($result)->toBeTrue();
    });
});
```

## 📚 Лучшие практики написания тестов

### 1. **Один тест - одна ответственность**

```php
// Хорошо
it('validates email format', function () {
    // Только проверка email
});

it('saves user to database', function () {
    // Только сохранение
});

// Плохо
it('validates and saves user', function () {
    // Две ответственности
});
```

### 2. **Читаемые названия тестов**

```php
// Хорошо
it('throws exception when file not found')
it('returns cached result on second call')
it('ignores files smaller than 1KB')

// Плохо
it('test file')
it('check cache')
it('small files')
```

### 3. **Изоляция зависимостей**

```php
describe('UserService', function () {
    beforeEach(function () {
        $this->db = Mockery::mock(Database::class);
        $this->service = new UserService($this->db);
    });

    it('creates user', function () {
        $this->db->shouldReceive('insert')->once()->andReturn(1);

        $result = $this->service->create(['name' => 'John']);

        expect($result)->toBe(1);
    });
});
```

### 4. **Тестируй поведение, не реализацию**

```php
// Хорошо: тест результата
it('sends welcome email after registration', function () {
    $user = $this->registerUser();
    expect($this->emailsSent())->toContain('welcome@site.com');
});

// Плохо: тест внутреннего состояния
it('calls mailer send method', function () {
    $this->spyOnMailer();
    $this->registerUser();
    expect($this->mailer->sendWasCalled())->toBeTrue();
});
```

### 5. **Используй фабрики для тестовых данных**

```php
describe('PostService', function () {
    it('publishes post', function () {
        $post = (new PostFactory())->create(['status' => 'draft']);
        $service = new PostService();

        $result = $service->publish($post['ID']);

        expect($result)->toBeTrue();
        expect($this->getPostStatus($post['ID']))->toBe('publish');
    });
});
```

### 6. **Тестируй edge cases**

```php
describe('FileProcessor', function () {
    it('handles empty files', function () {
        $result = $this->processor->process('');
        expect($result)->toBeFalse();
    });

    it('handles non-existent files', function () {
        $result = $this->processor->process('/non/existent/file.txt');
        expect($result)->toBeFalse();
    });

    it('handles files without permissions', function () {
        $file = $this->createFileWithoutPermissions();
        $result = $this->processor->process($file);
        expect($result)->toBeFalse();
    });
});
```

### 7. **Используй data providers для похожих тестов**

```php
describe('Calculator', function () {
    it('adds numbers correctly', function ($a, $b, $expected) {
        $result = $this->calculator->add($a, $b);
        expect($result)->toBe($expected);
    })->with([
        [1, 2, 3],
        [0, 0, 0],
        [-1, 1, 0],
        [100, 200, 300],
    ]);
});
```

## 🛠️ Инструменты и утилиты

### Mockery для моков

```php
$mock = Mockery::mock(SomeClass::class);
$mock->shouldReceive('method')->andReturn('value');
```

### Фабрики для данных

```php
// Создание тестовых данных
$user = (new UserFactory())->create();
$post = (new PostFactory())->create(['author' => $user['ID']]);
```

### DatabaseMigrations для БД тестов

```php
class MyTest extends TestCase
{
    use DatabaseMigrations;

    public function testCreatesRecord()
    {
        // БД очищается перед каждым тестом
        $this->createPost(['title' => 'Test']);

        $count = $this->getPostsCount();
        expect($count)->toBe(1);
    }
}
```

## 🔧 Настройка тестового окружения

### Bootstrap.php

Автоматически настраивает:
- WordPress константы (ABSPATH, WPINC и т.д.)
- WordPress функции (wp_die, apply_filters, add_action)
- Базу данных в памяти
- Моки для внешних зависимостей

### TestCase.php

Предоставляет:
- `runDatabaseMigrations()` - очистка БД
- `createMockAssetOptimizationService()` - моки сервисов
- `createTempFile()` - создание временных файлов
- `mockWpStyles()` / `mockWpScripts()` - моки WP очередей

## 🚨 Обработка проблемных тестов

Некоторые тесты могут конфликтовать с CI средой:

```php
// Пропуск в CI
if (getenv('CI') === 'true') {
    test('skipped in CI', function () {})->skip('Reason');
    return;
}
```

Или группировка:

```php
/**
 * @group problematic
 */
describe('Complex Test', function () {
    // Этот тест будет пропущен в CI
});
```

Запуск без проблемных:

```bash
composer test -- --exclude-group problematic
```

## 📊 Метрики качества

- **Покрытие кода**: > 80%
- **Время выполнения**: < 30 сек
- **Количество тестов**: > 20
- **Процент passing**: 100%

## 🎓 Продвинутые темы

### Mutation Testing

```bash
# Проверка качества тестов
composer test:mutation
```

### Property-based Testing

```php
it('works with any valid input', function ($input) {
    $result = $this->service->process($input);
    expect($result)->toBeValid();
})->with($this->generateRandomInputs());
```

### Performance Testing

```php
it('processes within time limit', function () {
    $start = microtime(true);
    $this->service->processLargeDataset();
    $duration = microtime(true) - $start;

    expect($duration)->toBeLessThan(1.0); // < 1 сек
});
```

## 📖 Ресурсы

- [Pest Documentation](https://pestphp.com/)
- [Mockery Documentation](https://docs.mockery.io/)
- [WordPress Testing Handbook](https://developer.wordpress.org/block-editor/contributors/code/testing-overview/)

---

## ✅ Результат

Следуя этому руководству, вы сможете:

1. **Писать качественные тесты** с хорошим покрытием
2. **Разрабатывать модули через TDD** с уверенностью
3. **Поддерживать код** без страха регрессий
4. **Интегрировать изменения** безопасно

**Помните**: Хорошо протестированный код = надежный код! 🚀
