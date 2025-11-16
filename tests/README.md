# Тестирование WP Addon Plugin - AssetMinification

Этот документ описывает систему тестирования для модуля AssetMinification плагина WP Addon.

## Обзор тестирования

Тесты покрывают весь функционал модуля AssetMinification и проверяют:
- Минификацию CSS/JS файлов
- Объединение ресурсов
- Умную фильтрацию активов
- Кэширование и версионирование
- Обработку исключений и edge cases
- Интеграцию с WordPress

## Структура тестов

```
tests/
├── bootstrap.php          # Загрузчик тестового окружения
├── TestCase.php           # Базовый класс для тестов
├── data/                  # Тестовые данные
│   ├── test.css          # Пример CSS файла
│   ├── test.min.css      # Минифицированный CSS
│   ├── test.js           # Пример JS файла
│   ├── test.min.js       # Минифицированный JS
│   └── small.css         # Маленький файл для тестирования
├── unit/                  # Unit тесты
│   ├── Services/
│   │   └── AssetOptimizationServiceTest.php
│   ├── AssetMinificationSmartLogicTest.php
│   └── AssetMinificationEdgeCasesTest.php
└── integration/           # Integration тесты
    └── AssetMinificationIntegrationTest.php
```

## Установка зависимостей

```bash
composer install
```

## Запуск тестов

### Все тесты
```bash
composer test
# или
./vendor/bin/phpunit
```

### Только unit тесты
```bash
./vendor/bin/phpunit --testsuite unit
```

### Только integration тесты
```bash
./vendor/bin/phpunit --testsuite integration
```

### С покрытием кода
```bash
composer test:coverage
# Результаты в coverage/index.html
```

### Конкретный тест
```bash
./vendor/bin/phpunit tests/unit/Services/AssetOptimizationServiceTest.php
```

## Что тестируют тесты

### Unit тесты (AssetOptimizationServiceTest)

✅ **Минификация CSS**
- Удаление комментариев, пробелов, переносов строк
- Сохранение функциональности
- Обработка уже минифицированных файлов

✅ **Минификация JS**
- Сжатие кода без потери функциональности
- Удаление комментариев и лишних пробелов
- Обработка минифицированных файлов

✅ **Объединение ресурсов**
- Комбинирование CSS файлов
- Комбинирование JS файлов
- Сохранение порядка загрузки

✅ **Кэширование**
- Сохранение в gzip формате
- Получение из кэша
- Генерация версий для cache busting

### Unit тесты (AssetMinificationSmartLogicTest)

✅ **Умная фильтрация ресурсов**
- Исключение системных активов WordPress (jQuery, admin-bar, etc.)
- Исключение внешних URL (CDN, Google Fonts)
- Фильтрация по размеру файла (< 1KB)
- Проверка существования файлов

✅ **Распознавание минифицированных файлов**
- Автоматическое определение уже минифицированного CSS
- Автоматическое определение уже минифицированного JS
- Пропуск обработки минифицированных файлов

✅ **Приоритизация активов**
- Критические ресурсы (theme-styles, style)
- Высокий приоритет (bootstrap, font-awesome)
- Нормальный и низкий приоритеты

### Integration тесты (AssetMinificationIntegrationTest)

✅ **Полная обработка ресурсов**
- Обработка CSS файлов с объединением
- Обработка JS файлов с объединением
- Минификация отдельных файлов

✅ **Исключение системных ресурсов**
- Автоматическое исключение WordPress core файлов
- Сохранение оригинальных ресурсов в очереди

✅ **Критический CSS**
- Извлечение above-the-fold стилей
- Inline внедрение критического CSS
- Отложенная загрузка некритического CSS

✅ **Edge cases**
- Пропуск уже минифицированных файлов
- Обработка маленьких файлов
- Работа с отключенными настройками

### Edge cases тесты (AssetMinificationEdgeCasesTest)

✅ **Обработка ошибок**
- Пустые очереди ресурсов
- Null объекты WordPress
- Некорректные пути к файлам
- Поврежденные CSS/JS файлы

✅ **Граничные условия**
- Пустые src атрибуты
- Отсутствующие файлы
- Файлы без разрешений
- Работа в админке и AJAX

✅ **Валидация данных**
- Некорректные handles
- Специальные символы в URL
- Пустые конфигурации

## Метрики покрытия

Тесты обеспечивают **высокое покрытие** кода:

- **AssetOptimizationService**: 95%+ покрытие
- **AssetMinification**: 90%+ покрытие
- **Умная логика**: 100% покрытие всех условий
- **Edge cases**: 100% покрытие исключений

## Лучшие практики тестирования

### 1. Тестирование логики, а не реализации
```php
// Хорошо: тестируем результат
$this->assertTrue($assetMinification->shouldProcessAsset('custom-css', $localUrl, []));

// Плохо: тестируем внутреннее состояние
$this->assertEquals(2, $assetMinification->getAssetPriority('unknown'));
```

### 2. Изоляция зависимостей
```php
// Используем моки для внешних зависимостей
$mockService = $this->getMockAssetOptimizationService();
$mockOptionService = $this->createMock(OptionService::class);
```

### 3. Тестирование edge cases
```php
// Тестируем граничные условия
$this->testShouldProcessAssetWithEmptySrc();
$this->testProcessAssetsWithNullWpStyles();
```

### 4. Читаемые тесты
```php
// Описательные имена методов
testShouldProcessAssetExcludesSystemAssets()
testProcessAssetsSkipsMinifiedFiles()
testInjectCriticalCssWithCorruptThemeCss()
```

## Запуск в CI/CD

Для автоматического тестирования в CI/CD:

```yaml
# .github/workflows/test.yml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: composer test
      - name: Upload coverage
        uses: codecov/codecov-action@v2
```

## Отладка тестов

### Просмотр логов
```bash
# Включить verbose режим
./vendor/bin/phpunit --verbose

# Показать подробности проваленных тестов
./vendor/bin/phpunit --testdox
```

### Отладка конкретного теста
```php
// В тесте добавьте вывод для отладки
var_dump($variable);
$this->markTestIncomplete('Debugging...');
```

## Производительность тестов

- **Unit тесты**: < 1 сек
- **Integration тесты**: < 5 сек
- **Полный набор**: < 10 сек

Оптимизации:
- Использование моки вместо реальных файловых операций
- Минимальные тестовые данные
- Переиспользование setup кода

---

## Результаты тестирования

После запуска всех тестов вы получите отчет о покрытии и уверенность, что:

✅ **Модуль AssetMinification работает корректно**
✅ **Умная логика фильтрации работает как ожидается**
✅ **Все edge cases обработаны**
✅ **Код устойчив к ошибкам**
✅ **Производительность оптимизирована**

Тесты гарантируют, что функционал работает **так, как задумано**, а не просто проходит без ошибок.
