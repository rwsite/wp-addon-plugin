# WordPress Excellence Plugin

🇷🇺 [Russian version](README.ru.md)

Transforms your standard WordPress installation into an excellent, optimized website with comprehensive performance, security, and usability enhancements.

## Key Features

### Performance Optimization
- **Asset Minification**: Automatic CSS and JavaScript minification with caching ✅
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

- **PSR-4 Autoloading**: Organized namespace structure
- **Modular Design**: Interface-based module system for easy extension
- **Dependency Injection**: Clean service architecture
- **Modern Testing**: Pest framework with declarative syntax, in-memory database, and data factories

## Installation

1. Download the plugin
2. Upload to `wp-content/plugins/`
3. Activate through WordPress admin
4. Configure settings in **WordPress Excellence** menu

## Requirements

- WordPress 5.6+
- PHP 7.4 - 8.4

## Testing

The plugin uses modern testing practices with Pest framework for declarative, readable tests.

### Quick Start
```bash
composer install
composer test  # Run all unit tests (83 tests)
composer test:coverage  # With coverage report
```

### Architecture
- **Pest Framework**: Declarative syntax with `describe`/`it`/`expect`
- **In-Memory SQLite**: Fast, isolated database testing
- **Data Factories**: Easy test data generation
- **CI/CD**: GitHub Actions with matrix testing (PHP 7.4-8.4)

### Writing Tests
```php
describe('AssetOptimizationService', function () {
    it('minifies CSS', function () {
        $service = new AssetOptimizationService(['minify_css' => true]);
        $result = $service->minifyCss('body { color: red; }');
        expect($result)->toBe('body{color:red}');
    });
});
```

Tests are located in `tests/Unit/` and `tests/Feature/`. All tests pass with 196 assertions.

## Development

- Follow PSR-12 coding standards
- Add PHPDoc for all public methods
- Write unit tests for new functionality
- Use modular system for new features

## Backwards Compatibility

The plugin maintains compatibility with existing installations. Legacy features are preserved while new features use the modular system.

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
