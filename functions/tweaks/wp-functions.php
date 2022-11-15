<?php
/*
1: Remove WP-Version im Header
2: WP-Emojis deaktivieren
3: Remove Windows Live Writer
4: Remove RSD-Link
5: Remove RSS links
6: Remove shortlink in the header
7: Remove adjacent links to posts in the header
8: Set limit post revisions to 5
9: Block http-requests by plugins/themes
10: Disable heartbeat
11: Disable Login-Error
12: Disable new themes on major WP updates
13: Disable the XML-RPC
14: Remove post by email function
15: Disable URL-fields on comments
16: Disable URL auto-linking in comments
17: Remove login-shake on errors
18: Empty WP-Trash every 14 days
19: Allow SVG type download
20: Disable Pingback
21: Disable adminbar in front for non admin users
22: Add VK, OK to profile
23: Show usages memory and time to generate page
24: Deregister widgets
25: Remove license.txt и readme.html files
26: Add filter to metabox of post taxonomies
27:
28:

 */



//Tweaks der eigentlichen Anpassungen
function wptweaker_setting_1()
{
    remove_action('wp_head', 'wp_generator'); // из заголовка
    add_filter('the_generator', '__return_empty_string'); // из фидов и URL
    if ( file_exists( ABSPATH . '/readme.txt'  ) )
        unlink ( ABSPATH . '/readme.txt' );
}

/** Disable Emo */
function wptweaker_setting_2()
{
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

    add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
    add_filter( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );
    function disable_emojis_tinymce( $plugins ) {
        if ( is_array( $plugins ) ) {
            return array_diff( $plugins, array( 'wpemoji' ) );
        }
    }

    function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
        if ( 'dns-prefetch' === $relation_type ) {
            // This filter is documented in wp-includes/formatting.php
            $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
            $urls = array_diff( $urls, array( $emoji_svg_url ) );
        }
        return $urls;
    }
}

function wptweaker_setting_3()
{
    remove_action('wp_head', 'wlwmanifest_link');
}

function wptweaker_setting_4()
{
    remove_action('wp_head', 'rsd_link');
}

function wptweaker_setting_5()
{
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
}

function wptweaker_setting_6()
{
    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
    remove_action('wp_head', 'wp_shortlink_header', 10, 0);
}

function wptweaker_setting_7()
{
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
}

function wptweaker_setting_8()
{
    define('WP_POST_REVISIONS', 5);
}

function wptweaker_setting_9()
{
    add_filter( 'pre_http_request', '__return_true', 100 );
}

function wptweaker_setting_10()
{
    add_action('init', 'stop_heartbeat', 1);
    function stop_heartbeat()
    {
        wp_deregister_script('heartbeat');
    }
}
function wptweaker_setting_11()
{
    /**
     * Remove jQuery Migrate script from the jQuery bundle only in front end.
     *
     * @since 1.0
     *
     * @param WP_Scripts $scripts WP_Scripts object.
     */
    function rw_remove_jquery_migrate( $scripts ) {
        if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
            $script = $scripts->registered['jquery'];
            if ( $script->deps ) {
                $script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
            }
        }
    }

    add_action( 'wp_default_scripts', 'rw_remove_jquery_migrate' );
}

function wptweaker_setting_12()
{
    define( 'CORE_UPGRADE_SKIP_NEW_BUNDLED', true );
}

function wptweaker_setting_13()
{
    add_filter('xmlrpc_enabled', '__return_false' );
}

function wptweaker_setting_14()
{
    add_filter( 'enable_post_by_email_configuration', '__return_false' );
}

function wptweaker_setting_15()
{
    if (is_admin()) {
        // отключим проверку обновлений при любом заходе в админку...
        remove_action('admin_init', '_maybe_update_core');
        remove_action('admin_init', '_maybe_update_plugins');
        remove_action('admin_init', '_maybe_update_themes');

        // отключим проверку обновлений при заходе на специальную страницу в админке...
        remove_action('load-plugins.php', 'wp_update_plugins');
        remove_action('load-themes.php', 'wp_update_themes');

        // оставим принудительную проверку при заходе на страницу обновлений...
        //remove_action( 'load-update-core.php', 'wp_update_plugins' );
        //remove_action( 'load-update-core.php', 'wp_update_themes' );

        // внутренняя страница админки "Update/Install Plugin" или "Update/Install Theme" - оставим не мешает...
        //remove_action( 'load-update.php', 'wp_update_plugins' );
        //remove_action( 'load-update.php', 'wp_update_themes' );

        // событие крона не трогаем, через него будет проверяться наличие обновлений - тут все отлично!
        //remove_action( 'wp_version_check', 'wp_version_check' );
        //remove_action( 'wp_update_plugins', 'wp_update_plugins' );
        //remove_action( 'wp_update_themes', 'wp_update_themes' );

        /**
         * отключим проверку необходимости обновить браузер в консоли - мы всегда юзаем топовые браузеры!
         * эта проверка происходит раз в неделю...
         * @see https://wp-kama.ru/function/wp_check_browser_version
         */
        add_filter('pre_site_transient_browser_' . md5($_SERVER['HTTP_USER_AGENT']), '__return_true');
    }
}

function wptweaker_setting_16()
{
    remove_filter('comment_text', 'make_clickable', 9);
}

function wptweaker_setting_17()
{
    function wpt_login_shake()
    {
        remove_action('login_head', 'wp_shake_js', 12);
    }
    add_action('login_head', 'wpt_login_shake');
}

function wptweaker_setting_18()
{
    define('EMPTY_TRASH_DAYS', 14 );
}

function wptweaker_setting_19(){
    function upload_allow_types( $mimes ) {
        // разрешаем новые типы
        $mimes['svg']  = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
        $mimes['doc']  = 'application/msword';
        $mimes['woff'] = 'font/woff';
        $mimes['psd']  = 'image/vnd.adobe.photoshop';
        $mimes['djv']  = 'image/vnd.djvu';
        $mimes['djvu'] = 'image/vnd.djvu';
        // отключаем имеющиеся
        unset( $mimes['mp4a'] );
        return $mimes;
    }
    add_filter( 'upload_mimes', 'upload_allow_types' );
}

function wptweaker_setting_20(){
    // Отключаем пинги на свои же посты
    add_action('pre_ping', 'kama_disable_inner_ping');
    function kama_disable_inner_ping( &$links ){
        foreach( $links as $k => $val ){
            if( false !== strpos( $val, str_replace('www.', '', $_SERVER['HTTP_HOST']) ) )
                unset( $links[$k] );
        }
    }
}

function wptweaker_setting_21()
{
    /* Отключение админ-бара для всех, кроме админа */
    function disable_admin_bar()
    {
        if ( ! current_user_can('edit_posts')) {
            add_filter('show_admin_bar', '__return_false');
            add_action('admin_print_scripts-profile.php', 'hide_admin_bar_settings');
        }
    }
    add_action('init', 'disable_admin_bar', 9);
}

function wptweaker_setting_22()
{
    // удаляет из профиля поля: AIM, Yahoo IM, Jabber / Add VK, OK
    add_filter('user_contactmethods', 'new_contactmethod', 10, 2);
    function new_contactmethod($methods, $user)
    {
        unset($methods['aim'], $methods['jabber'], $methods['yim']);
        $methods['vk'] = __('VK', 'wp-addon');
        $methods['ok'] = __('OK', 'wp-addon');
        return $methods;
    }
}

function wptweaker_setting_23()
{
    /* Выводит данные о кол-ве запросов к БД, время выполнения скрипта и размер затраченной памяти. */
    add_filter('admin_footer_text', 'performance'); // в подвале админки
    add_filter('wp_footer', 'performance');
    function performance(){
        $stat = sprintf( __('SQL: %d за %s sec. %.2f MB ', 'wp-addon'), get_num_queries(), timer_stop( 0 ),
            (memory_get_peak_usage() / 1024 / 1024));
        if (is_admin()) {
            echo $stat; // видно
        } elseif(current_user_can('manage_options')) {
            echo '<div id="site-stats" class="site-stats">' . $stat . '</div>';
            echo '<style>#site-stats{
                        text-align: center;
                        color: rgb(255, 255, 255);
                        background: #222222;
                        width: 100%;
                        height: auto;
                        padding: 20px;
                        display: flex;
                        align-items: center;
                        justify-content: space-around;
                }</style>';
        }
    }
}

function wptweaker_setting_24()
{
    /* deregister widgets */
    add_action('widgets_init', 'unregister_basic_widgets');
    function unregister_basic_widgets()
    {
        unregister_widget('WP_Widget_Pages');            // Виджет страниц
        unregister_widget('WP_Widget_Calendar');         // Календарь
        unregister_widget('WP_Widget_Archives');         // Архивы
        unregister_widget('WP_Widget_Links');            // Ссылки
        unregister_widget('WP_Widget_Meta');             // Мета виджет
        unregister_widget('WP_Widget_Search');           // Поиск
        unregister_widget('WP_Widget_Text');             // Текст
        unregister_widget('WP_Widget_Categories');       // Категории
        unregister_widget('WP_Widget_Recent_Posts');     // Последние записи
        unregister_widget('WP_Widget_Recent_Comments');  // Последние комментарии
        unregister_widget('WP_Widget_RSS');              // RSS
        unregister_widget('WP_Widget_Tag_Cloud');        // Облако меток
        unregister_widget('WP_Nav_Menu_Widget');         // Меню
    }
}

function wptweaker_setting_25()
{
    ## Удаление файлов license.txt и readme.html для защиты
    if (is_admin() && ! defined('DOING_AJAX')) {
        $license_file = ABSPATH . '/license.txt';
        $readme_file  = ABSPATH . '/readme.html';

        if (file_exists($license_file) && current_user_can('manage_options')) {
            $deleted = unlink($license_file) && unlink($readme_file);

            if ( ! $deleted) {
                $GLOBALS['readmedel'] = 'Не удалось удалить файлы: license.txt и readme.html из папки `' . ABSPATH . '`. Удалите их вручную!';
            } else {
                $GLOBALS['readmedel'] = 'Файлы: license.txt и readme.html удалены из из папки `' . ABSPATH . '`.';
            }

            add_action('admin_notices', function () {
                echo '<div class="error is-dismissible"><p>' . $GLOBALS['readmedel'] . '</p></div>';
            });
        }
    }
}

function wptweaker_setting_26()
{
    ## Фильтр элементо втаксономии для метабокса таксономий в админке.
    ## Позволяет удобно фильтровать (искать) элементы таксономии по назанию, когда их очень много
    add_action('admin_print_scripts', 'my_admin_term_filter', 99);
    function my_admin_term_filter()
    {
        $screen = get_current_screen();

        if ($screen === null || 'post' !== $screen->base) {
            return;
        } // только для страницы редактирвоания любой записи
        ?>
        <script>
            jQuery(document).ready(function ($) {
                var $categoryDivs = $('.categorydiv');
                $categoryDivs.prepend('<input type="search" class="fc-search-field" placeholder="<?=__('filter...', 'wp-addon')?>" style="width:100%" />');
                $categoryDivs.on('keyup search', '.fc-search-field', function (event) {
                    var searchTerm = event.target.value,
                        $listItems = $(this).parent().find('.categorychecklist li');

                    if ($.trim(searchTerm)) {
                        $listItems.hide().filter(function () {
                            return $(this).text().toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1;
                        }).show();
                    } else {
                        $listItems.show();
                    }
                });
            });
        </script>
        <?php
    }
}

function wptweaker_setting_27()
{
    ##  отменим показ выбранного термина наверху в checkbox списке терминов
    add_filter('wp_terms_checklist_args', 'set_checked_ontop_default', 10);
    function set_checked_ontop_default($args)
    {
        // изменим параметр по умолчанию на false
        if ( ! isset($args['checked_ontop'])) {
            $args['checked_ontop'] = false;
        }

        return $args;
    }
}


function wptweaker_setting_28()
{
    add_action( 'admin_menu', 'add_user_menu_bubble' );
    function add_user_menu_bubble(){
        global $menu;
        $count = wp_count_posts()->pending; // на утверждении
        if( $count ){
            foreach( $menu as $key => $value ){
                if( $menu[$key][2] === 'edit.php' ){
                    $menu[$key][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . $count . '</span></span>';
                    break;
                }
            }
        }
    }
}

function wptweaker_setting_29()
{
    // Удаляем уведомление об обновлении WordPress для всех кроме админа
    function disable_notice_for_users() {
        if ( ! current_user_can('manage_options')) {
            remove_action('admin_notices', 'update_nag', 3);
            remove_action('admin_notices', 'maintenance_nag', 10);
        }
    }
    add_action('admin_head', 'disable_notice_for_users');
}

function wptweaker_setting_30()
{
    ## Ссылка «Читать далее...» после цитаты в цикле. Замена `[...]`
    add_filter('excerpt_more', 'replace_excerpt_func', 99);
    function replace_excerpt_func($more)
    {
        global $post;
        return '<a class="read_more" href="' . get_permalink($post) . '">' . __('Read more ...', 'wp-addon') . '</a>';
    }
}

function wptweaker_setting_31()
{
    ## Шорткоды в виджете "Текст"
    if ( ! is_admin()) {
        add_filter('widget_text', 'do_shortcode', 11);
    }
}

function wptweaker_setting_32()
{
    ## Изменяет URL расположения jQuery файла только для фронт-энда
    add_action('rw_enqueue_scripts', 'jquery_enqueue_func');
    function jquery_enqueue_func(){
        wp_deregister_script('jquery');
        wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js', false, false);
        wp_enqueue_script('jquery');
    }
}

/**
 * Remove auto update core theme ad plugins
 * @return void
 */
function wptweaker_settimngs_33(){
    $names = [
	    'auto_update_core_dev', 'auto_update_core_minor', 'auto_update_core_major'
    ];
    foreach ($names as $option_name) {
	    add_filter( "pre_site_option_{$option_name}", function (){
            return 'disabled';
        } );
    }
    $option_name = 'dismissed_update_core';
	add_filter( "pre_site_option_{$option_name}", function (){
		return false;
	} );

	add_filter( 'automatic_updater_disabled', '__return_true' );
	add_filter( 'pre_site_transient_update_core',function (){
        $obj = new stdClass();
        $obj->response = 'autoupdate';
		return (object) [
			'updates'         => [
                '0' => $obj
			],
			'version_checked' => get_bloginfo( 'version' ),
			'version'         => get_bloginfo( 'version' ),
		];
    });
}