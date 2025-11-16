<?php

namespace WpAddon;

use WpAddon\Services\MediaCleanupService;

defined( 'ABSPATH' ) or exit;


class WP_Addon_Settings {

	private static $instance;
	public $file;
	public $path;
	public $url;
	public $ver;
	public $wp_plugin_name;
	public $wp_plugin_slug;

	private function __construct() {
		$this->file = RW_FILE;
		$this->path = RW_PLUGIN_DIR;
		$this->url  = RW_PLUGIN_URL;
		$this->ver  = '1.1.3';
	}

    public static function getInstance(): WP_Addon_Settings
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

	public function __clone() {
	}

	public function __wakeup() {
	}

    public function add_actions()
    {
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme'] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ], 20 );
    }

	/**
	 * style and scripts in wp-admin
	 *
	 * @param $page
	 */
	public function admin_assets( $page ) {
		if ( false === strpos( $page, $this->wp_plugin_slug ) ) {
			return;
		}

		wp_enqueue_style( $this->wp_plugin_slug,
			RW_PLUGIN_URL . 'assets/css/min/admin.min.css',
			false,
			$this->ver,
			'all' );
	}

	/**
	 * @see  http://codestarframework.com/documentation/#/fields?id=checkbox
	 */
	public function after_setup_theme() {

		// Check core class for avoid errors
		if ( !class_exists( 'CSF' ) ){
            return;
        }

        $this->wp_plugin_name = __('Wordpress Addon', 'wp-addon');
        $this->wp_plugin_slug = 'wp-addon';

        // Set a unique slug-like ID
        $prefix = $this->wp_plugin_slug;

        // Create options
        \CSF::createOptions($prefix, require_once __DIR__ . '/_options.php');

        // General Settings
        \CSF::createSection($prefix, [
            'title'  => __('General Settings', 'wp-addon'),
            'icon'   => 'fa fa-rocket',
            'fields' => require_once __DIR__ . '/main.php',
        ]);

        // Tweaks
        \CSF::createSection($prefix, [
            'title'  => __('Tweaks', 'wp-addon'),
            'icon'   => 'fa fa-wordpress',
            'fields' => require_once __DIR__ . '/tweaks.php',
        ]);

        // Cache
        \CSF::createSection($prefix, [
            'title'  => __('Cache', 'wp-addon'),
            'icon'   => 'fa fa-database',
            'description' => __('Кэширование страниц - это сохранение готовых HTML-страниц в файл. Когда пользователь заходит на сайт, вместо выполнения PHP-кода и запросов к БД, ему сразу показывается сохраненная страница. Это в 5-10 раз ускоряет загрузку сайта.<br><br><strong>Когда использовать:</strong> На готовом сайте с большим трафиком. <strong>Когда отключать:</strong> Во время разработки или если контент часто меняется.<br><br><strong>Важно:</strong> Кэшированные страницы хранятся в папке wp-content/cache/pages/ как .gz файлы.', 'wp-addon'),
            'fields' => [
                [
                    'id'    => 'cache_enabled',
                    'type'  => 'switcher',
                    'title' => __('Включить кэширование страниц', 'wp-addon'),
                    'desc'  => __('Главная кнопка включения/выключения кэша. Когда ВКЛЮЧЕНО: все страницы сайта сохраняются в кэш. Когда ВЫКЛЮЧЕНО: кэш не используется, страницы генерируются каждый раз заново.', 'wp-addon'),
                    'default' => true,
                ],
                [
                    'id'    => 'cache_ttl',
                    'type'  => 'number',
                    'title' => __('Время жизни кэша (секунды)', 'wp-addon'),
                    'desc'  => __('Сколько секунд хранить кэшированную страницу. После этого времени страница будет создана заново. Для новостного сайта - 1800 сек (30 мин). Для статичного - 3600 сек (1 час).', 'wp-addon'),
                    'default' => 3600,
                    'min'   => 300,
                    'max'   => 86400,
                ],
                [
                    'id'    => 'cache_exclude_logged_in',
                    'type'  => 'switcher',
                    'title' => __('Не кэшировать для залогиненных пользователей', 'wp-addon'),
                    'desc'  => __('Если пользователь вошел в админку или личный кабинет - показывать ему свежие страницы без кэша. Иначе он может не видеть свои изменения или уведомления.', 'wp-addon'),
                    'default' => true,
                ],
                [
                    'id'    => 'cache_exclude_urls',
                    'type'  => 'textarea',
                    'title' => __('Не кэшировать эти страницы', 'wp-addon'),
                    'desc'  => __('Страницы, которые меняются часто и не должны кэшироваться. Одна строка - один URL. Примеры: /wp-admin/ (админка), /checkout/ (оформление заказа), /cart/ (корзина), /my-account/ (личный кабинет).', 'wp-addon'),
                    'default' => "/wp-admin/\n/wp-login.php\n/checkout/\n/cart/",
                ],
                [
                    'id'    => 'cache_preload_pages',
                    'type'  => 'textarea',
                    'title' => __('Предварительно загружать эти страницы', 'wp-addon'),
                    'desc'  => __('Страницы для авто-кэширования каждый час. <strong>Оставьте пустым для автоматического режима:</strong> будут загружаться главная страница + все страницы из главного меню сайта (до 10 шт). Или укажите вручную: один URL на строку, например /about/, /services/', 'wp-addon'),
                    'default' => "",
                ],
                [
                    'id'    => 'cache_clear_on_post_save',
                    'type'  => 'switcher',
                    'title' => __('Очищать кэш при публикации статей', 'wp-addon'),
                    'desc'  => __('Когда вы публикуете новую статью или редактируете старую - автоматически удалить весь кэш. Так читатели сразу увидят свежий контент. Отключите, если публикуете часто - это замедлит сайт.', 'wp-addon'),
                    'default' => true,
                ],
            ],
        ]);

        // Asset Minification
        \CSF::createSection($prefix, [
            'title'  => __('Asset Minification', 'wp-addon'),
            'icon'   => 'fa fa-compress',
            'description' => __('Оптимизация ресурсов - это комплексная система улучшения производительности сайта путем минификации и объединения CSS/JavaScript файлов. Модуль автоматически анализирует все подключаемые ресурсы и применяет оптимальные стратегии оптимизации.<br><br><strong>Преимущества:</strong><br>• Снижение размера файлов на 20-40%<br>• Уменьшение количества HTTP-запросов<br>• Ускорение загрузки страниц<br>• Лучшие показатели в PageSpeed Insights<br><br><strong>Автоматическая логика:</strong><br>• Исключает системные ресурсы WordPress<br>• Не обрабатывает файлы размером менее 1KB<br>• Пропускает уже минифицированные файлы<br>• Анализирует приоритеты загрузки ресурсов', 'wp-addon'),
            'fields' => [
                [
                    'id'    => 'asset_minification_enabled',
                    'type'  => 'switcher',
                    'title' => __('Включить оптимизацию ресурсов', 'wp-addon'),
                    'desc'  => __('Главный переключатель модуля оптимизации. При включении активируется интеллектуальная обработка всех CSS и JavaScript ресурсов сайта. Рекомендуется включать на production-сайтах для максимальной производительности.', 'wp-addon'),
                    'default' => true,
                ],
                [
                    'id'    => 'asset_minify_css',
                    'type'  => 'switcher',
                    'title' => __('Минифицировать CSS файлы', 'wp-addon'),
                    'desc'  => __('Удаляет из CSS файлов: комментарии, лишние пробелы, переносы строк и табуляцию. Не обрабатывает файлы, которые уже минифицированы или имеют размер менее 1KB. Экономия трафика: 15-30% на файл.', 'wp-addon'),
                    'default' => true,
                    'dependency' => ['asset_minification_enabled', '==', 'true'],
                ],
                [
                    'id'    => 'asset_minify_js',
                    'type'  => 'switcher',
                    'title' => __('Минифицировать JavaScript файлы', 'wp-addon'),
                    'desc'  => __('Сжимает JS код, удаляя комментарии, лишние пробелы и форматирование. Пропускает минифицированные файлы и файлы менее 1KB. Важно: проверяйте работоспособность после включения, так как некоторые плагины могут иметь чувствительный к минификации код.', 'wp-addon'),
                    'default' => true,
                    'dependency' => ['asset_minification_enabled', '==', 'true'],
                ],
                [
                    'id'    => 'asset_combine_css',
                    'type'  => 'switcher',
                    'title' => __('Объединять CSS файлы', 'wp-addon'),
                    'desc'  => __('Собирает все подходящие CSS файлы в один объединенный файл, уменьшая количество HTTP-запросов к серверу. Автоматически исключает системные стили WordPress. Эффективно для сайтов с 3+ CSS файлами.', 'wp-addon'),
                    'default' => true,
                    'dependency' => ['asset_minification_enabled', '==', 'true'],
                ],
                [
                    'id'    => 'asset_combine_js',
                    'type'  => 'switcher',
                    'title' => __('Объединять JavaScript файлы', 'wp-addon'),
                    'desc'  => __('Комбинирует JS файлы в один, загружаемый в футере. Уменьшает количество запросов, но может нарушить порядок загрузки. Рекомендуется тестировать на наличие JavaScript ошибок после включения.', 'wp-addon'),
                    'default' => true,
                    'dependency' => ['asset_minification_enabled', '==', 'true'],
                ],
                [
                    'id'    => 'asset_critical_css_enabled',
                    'type'  => 'switcher',
                    'title' => __('Внедрять критический CSS', 'wp-addon'),
                    'desc'  => __('Автоматически извлекает и внедряет inline критические CSS стили (шапка, меню, основной контент) для мгновенного отображения above-the-fold контента. Улучшает показатель First Contentful Paint в Lighthouse.', 'wp-addon'),
                    'default' => true,
                    'dependency' => ['asset_minification_enabled', '==', 'true'],
                ],
                [
                    'id'    => 'asset_defer_non_critical_css',
                    'type'  => 'switcher',
                    'title' => __('Откладывать некритический CSS', 'wp-addon'),
                    'desc'  => __('Загружает некритические CSS файлы асинхронно после отрисовки страницы. Предотвращает блокировку рендеринга, но может вызвать кратковременное "моргание" нестилизованного контента (FOUC).', 'wp-addon'),
                    'default' => true,
                    'dependency' => ['asset_minification_enabled', '==', 'true'],
                ],
                [
                    'id'    => 'asset_exclude_css',
                    'type'  => 'textarea',
                    'title' => __('Исключить CSS файлы', 'wp-addon'),
                    'desc'  => __('Список handles CSS файлов через запятую, которые не нужно оптимизировать. Примеры: critical-styles, admin-css, custom-admin-styles. Системные файлы WordPress исключаются автоматически.', 'wp-addon'),
                    'default' => 'admin-bar,dashicons',
                    'dependency' => ['asset_minification_enabled', '==', 'true'],
                ],
                [
                    'id'    => 'asset_exclude_js',
                    'type'  => 'textarea',
                    'title' => __('Исключить JavaScript файлы', 'wp-addon'),
                    'desc'  => __('Handles JS файлов через запятую для исключения из оптимизации. Примеры: google-analytics, facebook-pixel, custom-scripts. Системные скрипты WordPress (jQuery, etc.) исключаются автоматически.', 'wp-addon'),
                    'default' => 'jquery,jquery-core',
                    'dependency' => ['asset_minification_enabled', '==', 'true'],
                ],
            ],
        ]);

        // Media Cleanup
        \CSF::createSection($prefix, [
            'title'  => __('Media Cleanup', 'wp-addon'),
            'icon'   => 'fa fa-image',
            'description' => __('This section allows you to clean up unused image sizes to free up disk space. WordPress generates multiple sizes for each uploaded image, but if your theme or plugins don\'t use all of them, they take up unnecessary space. Use this tool to identify and remove such files.<br><br><strong>When to use:</strong> After changing themes, disabling plugins that generate custom sizes, or optimizing site performance.<br><br><strong>Precautions:</strong> Always create a backup before cleanup. Use "Preview Cleanup" first to see what will be deleted. The tool preserves original images and "scaled" versions (up to 2000px). Deleted files cannot be recovered!', 'wp-addon'),
            'fields' => [
                [
                    'id'      => 'cleanup_images',
                    'type'    => 'content',
                    'title'   => __('Clean up unused image sizes', 'wp-addon'),
                    'content' => '<p>' . sprintf(__('This will delete all image sizes except: %s. Files will be deleted permanently!', 'wp-addon'), implode(', ', MediaCleanupService::getRegisteredSizesStatic())) . '</p><button id="preview-cleanup-btn" class="button">' . __('Preview Cleanup', 'wp-addon') . '</button> <button id="cleanup-images-btn" class="button button-primary">' . __('Start Cleanup', 'wp-addon') . '</button><div id="cleanup-result"></div><script>jQuery(document).ready(function($){$("#preview-cleanup-btn").click(function(e){e.preventDefault();$("#cleanup-result").html("' . __('Loading preview...', 'wp-addon') . '");$.post(ajaxurl,{action:"wp_addon_cleanup_images_dry_run",nonce:"'.wp_create_nonce('cleanup_images').'"},function(r){$("#cleanup-result").html(r);});});$("#cleanup-images-btn").click(function(e){e.preventDefault();if(confirm("' . __('Are you sure? This action cannot be undone.', 'wp-addon') . '")){$("#cleanup-result").html("' . __('Processing...', 'wp-addon') . '");$.post(ajaxurl,{action:"wp_addon_cleanup_images",nonce:"'.wp_create_nonce('cleanup_images').'"},function(r){$("#cleanup-result").html(r);});}});});</script>',
                ],
            ],
        ]);

        // Redirects
        \CSF::createSection($prefix, [
            'title'  => __('Redirects', 'wp-addon'),
            'icon'   => 'fa fa-share',
            'description' => __('Управление редиректами 301. Создавайте правила перенаправления с одного URL на другой. Поддерживается как простое перенаправление, так и использование wildcard (*) для перенаправления папок.<br><br><strong>Простые редиректы:</strong> /old-page/ → /new-page/<br><strong>Wildcard редиректы:</strong> /old-folder/* → /new-folder/*<br><br><strong>Важно:</strong> Редиректы применяются ко всем запросам, кроме wp-admin и wp-login для предотвращения блокировки доступа к админке.', 'wp-addon'),
            'fields' => [
                [
                    'id'    => 'redirects_wildcard',
                    'type'  => 'switcher',
                    'title' => __('Использовать wildcard редиректы', 'wp-addon'),
                    'desc'  => __('Включите для поддержки символа * в URL. Пример: /old-folder/* будет перенаправлять все страницы из old-folder в соответствующие страницы new-folder.', 'wp-addon'),
                    'default' => false,
                ],
                [
                    'id'    => 'redirects_rules',
                    'type'  => 'repeater',
                    'title' => __('Правила редиректов', 'wp-addon'),
                    'desc'  => __('Добавьте правила перенаправления. Request - исходный URL (относительно корня сайта), Destination - целевой URL.', 'wp-addon'),
                    'fields' => [
                        [
                            'id'    => 'request',
                            'type'  => 'text',
                            'title' => __('Request URL', 'wp-addon'),
                            'desc'  => __('Исходный URL для перенаправления. Пример: /old-page/ или /old-folder/*', 'wp-addon'),
                            'attributes' => [
                                'placeholder' => '/old-page/',
                            ],
                        ],
                        [
                            'id'    => 'destination',
                            'type'  => 'text',
                            'title' => __('Destination URL', 'wp-addon'),
                            'desc'  => __('Целевой URL. Может быть относительным (/new-page/) или абсолютным (https://example.com/new-page/)', 'wp-addon'),
                            'attributes' => [
                                'placeholder' => '/new-page/',
                            ],
                        ],
                    ],
                    'default' => [],
                ],
            ],
        ]);

        // Shortcodes and Widgets
        \CSF::createSection($prefix, [
            'title'  => __('Shortcodes and Widgets', 'wp-addon'),
            'icon'   => 'fa fa-bolt',
            'fields' => require __DIR__ . '/wp-widgets.php',
        ]);

        do_action('wp_addon_settings_section', $prefix);

        // Custom Code
        \CSF::createSection($prefix, [
            'title'  => __('Custom code', 'wp-addon'),
            'icon'   => 'fa fa-code',
            'fields' => [
                [
                    'id'       => 'rw_header_css',
                    'type'     => 'code_editor',
                    'title'    => __('CSS Code in Header', 'wp-addon'),
                    'settings' => [
                        'theme' => 'mbo',
                        'mode'  => 'css',
                    ],
                    'sanitize' => false,
                ],
                [
                    'id'       => 'rw_header_html',
                    'type'     => 'code_editor',
                    'title'    => __('Any HTML code or Analytics code in header.',
                        'wp-addon'),
                    'settings' => [
                        'theme' => 'monokai',
                        'mode'  => 'htmlmixed',
                    ],
                    'default'  => '',
                    'sanitize' => false,
                ],
                [
                    'id'       => 'rw_footer_html',
                    'type'     => 'code_editor',
                    'title'    => __('Any HTML code in footer.', 'wp-addon'),
                    'settings' => [
                        'theme' => 'monokai',
                        //'mode'  => 'php',
                    ],
                    'default'  => '',
                    'sanitize' => false,
                ],
            ],// #fields
        ]);

        // BackUp
        \CSF::createSection($prefix, [
            'title'  => __('Backup Settings', 'wp-addon'),
            'icon'   => 'fa fa-server',
            'fields' => [
                [
                    'title' => __('Download settings now', 'wp-addon'),
                    'desc'  => __('You can get or set settings from backup'),
                    'type'  => 'backup',
                ],
            ],
        ]);
	}
}
