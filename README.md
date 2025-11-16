# WP Excellence Addon

[![GitHub release](https://img.shields.io/github/release/rwsite/wp-addon-plugin.svg?style=flat-square)](https://github.com/rwsite/wp-addon-plugin/releases)
[![PHP Version](https://img.shields.io/badge/PHP-8.2+-blue.svg?style=flat-square)](https://php.net)
[![WordPress Version](https://img.shields.io/badge/WordPress-6.6+-blue.svg?style=flat-square)](https://wordpress.org)
[![License](https://img.shields.io/github/license/rwsite/wp-addon-plugin.svg?style=flat-square)](LICENSE)

🇷🇺 [Russian version](README.ru.md)

Transform your standard WordPress installation into an excellent, optimized website with advanced performance enhancements.

## ✨ Key Features

### 🚀 Performance Optimization
- **Asset Minification**: Automatic CSS and JavaScript minification with intelligent caching
- **Smart Processing**: Only processes local assets, skips already minified files
- **Cache Management**: Automatic cleanup on theme/plugin updates

## 🏗️ Architecture

- **PSR-4 Autoloading**: Clean namespace structure
- **Modular Design**: Interface-based system for easy extension
- **Modern Testing**: Pest framework with declarative syntax
- **CI/CD**: GitHub Actions with matrix testing

## 📦 Installation

1. Download the plugin
2. Upload to `wp-content/plugins/`
3. Activate through WordPress admin
4. Configure settings in **WP Excellence Addon** menu

## ✅ Requirements

- WordPress 6.6+
- PHP 8.2+

## 🧪 Testing

Modern testing with Pest framework:

```bash
composer install
composer test  # Run unit tests
composer test:coverage  # With coverage report
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## 📄 License

See LICENSE file for details.

## 🆘 Support

- [Settings Guide](SETTINGS.md)
- [Module Development](MODULES_GUIDE.md)
