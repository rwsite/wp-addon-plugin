<?php

use WpAddon\Interfaces\ModuleInterface;
use WpAddon\Traits\HookTrait;

class PerformanceTweaks implements ModuleInterface {
    use HookTrait;

    private function getSetting($key, $default = null) {
        $options = get_option('wp-addon');
        return isset($options[$key]) ? $options[$key] : $default;
    }

    public function init(): void {
        // Header cleanup
        if ($this->getSetting('wptweaker_setting_1', true)) {
            $this->addHook('init', [$this, 'removeWpVersion']);
        }
        if ($this->getSetting('wptweaker_setting_2', true)) {
            $this->addHook('init', [$this, 'disableEmojis']);
        }
        if ($this->getSetting('wptweaker_setting_3', true)) {
            $this->addHook('init', [$this, 'removeWindowsLiveWriter']);
        }
        if ($this->getSetting('wptweaker_setting_4', true)) {
            $this->addHook('init', [$this, 'removeRsdLink']);
        }
        if ($this->getSetting('wptweaker_setting_5', true)) {
            $this->addHook('init', [$this, 'removeRssLinks']);
        }
        if ($this->getSetting('wptweaker_setting_6', true)) {
            $this->addHook('init', [$this, 'removeShortlink']);
        }
        if ($this->getSetting('wptweaker_setting_7', true)) {
            $this->addHook('init', [$this, 'removeAdjacentLinks']);
        }

        // Content optimization
        if ($this->getSetting('wptweaker_setting_8', true)) {
            $this->addFilter('wp_revisions_to_keep', [$this, 'limitRevisions']);
        }
        if ($this->getSetting('wptweaker_setting_9', false)) {
            $this->addHook('init', [$this, 'blockHttpRequests']);
        }
        if ($this->getSetting('wptweaker_setting_10', false)) {
            $this->addHook('init', [$this, 'disableHeartbeat']);
        }
        if ($this->getSetting('wptweaker_setting_11', false)) {
            $this->addHook('wp_enqueue_scripts', [$this, 'removeJqueryMigrate']);
        }

        // System tweaks
        if ($this->getSetting('wptweaker_setting_12', true)) {
            $this->addFilter('auto_update_theme', '__return_false');
        }
        if ($this->getSetting('wptweaker_setting_13', true)) {
            $this->addHook('init', [$this, 'disableXmlRpc']);
        }
        if ($this->getSetting('wptweaker_setting_14', true)) {
            $this->addHook('init', [$this, 'removePostByEmail']);
        }
        if ($this->getSetting('wptweaker_setting_15', true)) {
            $this->addHook('init', [$this, 'disableAggressiveUpdates']);
        }
        if ($this->getSetting('wptweaker_setting_16', true)) {
            $this->addFilter('comment_text', [$this, 'disableCommentUrlAutolinking']);
        }
        if ($this->getSetting('wptweaker_setting_17', true)) {
            $this->addHook('login_head', [$this, 'removeLoginShake']);
        }
        if ($this->getSetting('wptweaker_setting_18', true)) {
            $this->addHook('init', [$this, 'autoEmptyTrash']);
        }
        if ($this->getSetting('wptweaker_setting_19', true)) {
            $this->addFilter('upload_mimes', [$this, 'allowAdditionalFileTypes']);
        }
        if ($this->getSetting('wptweaker_setting_20', true)) {
            $this->addFilter('xmlrpc_enabled', '__return_false');
        }
        if ($this->getSetting('wptweaker_setting_21', true)) {
            $this->addHook('init', [$this, 'hideAdminBar']);
        }
        if ($this->getSetting('wptweaker_setting_22', true)) {
            $this->addHook('user_contactmethods', [$this, 'addSocialFields']);
        }
        if ($this->getSetting('wptweaker_setting_23', true)) {
            $this->addHook('wp_footer', [$this, 'showPerformanceInfo']);
        }
        if ($this->getSetting('wptweaker_setting_24', false)) {
            $this->addHook('widgets_init', [$this, 'removeDefaultWidgets']);
        }
        if ($this->getSetting('wptweaker_setting_25', false)) {
            $this->addHook('init', [$this, 'autoRemoveFiles']);
        }
        if ($this->getSetting('wptweaker_setting_26', true)) {
            $this->addHook('admin_notices', [$this, 'pendingPostsNotice']);
        }
        if ($this->getSetting('wptweaker_setting_27', true)) {
            $this->addHook('admin_menu', [$this, 'disableTaxonomyDropdown']);
        }
        if ($this->getSetting('wptweaker_setting_28', true)) {
            $this->addFilter('wp_count_posts', [$this, 'showPendingCount']);
        }
        if ($this->getSetting('wptweaker_setting_29', true)) {
            $this->addHook('admin_head', [$this, 'adminUpdateNotices']);
        }
        if ($this->getSetting('wptweaker_setting_30', true)) {
            $this->addFilter('excerpt_more', [$this, 'changeExcerptMore']);
        }
        if ($this->getSetting('wptweaker_setting_31', true)) {
            $this->addHook('init', [$this, 'allowShortcodesInWidgets']);
        }
        if ($this->getSetting('wptweaker_setting_32', false)) {
            $this->addHook('init', [$this, 'jqueryFromGoogle']);
        }
        if ($this->getSetting('wptweaker_setting_33', false)) {
            $this->addFilter('the_content', [$this, 'removeAutoP']);
        }
        if ($this->getSetting('wptweaker_setting_34', true)) {
            $this->addFilter('upload_mimes', [$this, 'allowWebp']);
        }
        if ($this->getSetting('wptweaker_setting_35', true)) {
            $this->addFilter('upload_mimes', [$this, 'allowSvg']);
        }
        if ($this->getSetting('wptweaker_setting_36', true)) {
            $this->addHook('admin_init', [$this, 'disableBrowserCheck']);
        }
    }

    // Implementation methods for each tweak
    public function removeWpVersion() {
        remove_action('wp_head', 'wp_generator');
    }

    public function disableEmojis() {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        add_filter('tiny_mce_plugins', function($plugins) {
            return array_diff($plugins, ['wpemoji']);
        });
    }

    public function removeWindowsLiveWriter() {
        remove_action('wp_head', 'wlwmanifest_link');
    }

    public function removeRsdLink() {
        remove_action('wp_head', 'rsd_link');
    }

    public function removeRssLinks() {
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'feed_links_extra', 3);
    }

    public function removeShortlink() {
        remove_action('wp_head', 'wp_shortlink_wp_head');
    }

    public function removeAdjacentLinks() {
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
    }

    public function limitRevisions() {
        return 5;
    }

    public function blockHttpRequests() {
        if (!defined('WP_HTTP_BLOCK_EXTERNAL')) {
            define('WP_HTTP_BLOCK_EXTERNAL', true);
        }
    }

    public function disableHeartbeat() {
        wp_deregister_script('heartbeat');
    }

    public function removeJqueryMigrate() {
        wp_deregister_script('jquery-migrate');
    }

    public function disableXmlRpc() {
        add_filter('xmlrpc_enabled', '__return_false');
    }

    public function removePostByEmail() {
        add_filter('enable_post_by_email_configuration', '__return_false');
    }

    public function disableAggressiveUpdates() {
        remove_action('wp_maybe_auto_update', 'wp_maybe_auto_update');
    }

    public function disableCommentUrlAutolinking($content) {
        return preg_replace('/<a href="(.*?)">(.*?)<\/a>/', '\\2', $content);
    }

    public function removeLoginShake() {
        remove_action('login_head', 'wp_shake_js', 12);
    }

    public function autoEmptyTrash() {
        if (!defined('EMPTY_TRASH_DAYS')) {
            define('EMPTY_TRASH_DAYS', 14);
        }
    }

    public function allowAdditionalFileTypes($mimes) {
        $mimes['svg'] = 'image/svg+xml';
        $mimes['doc'] = 'application/msword';
        $mimes['djv'] = 'image/vnd.djvu';
        return $mimes;
    }

    public function hideAdminBar() {
        if (!current_user_can('administrator')) {
            add_filter('show_admin_bar', '__return_false');
        }
    }

    public function addSocialFields($contactmethods) {
        $contactmethods['vk'] = 'VK Profile';
        $contactmethods['ok'] = 'Odnoklassniki Profile';
        return $contactmethods;
    }

    public function showPerformanceInfo() {
        echo '<div style="text-align:center;font-size:11px;color:#999;">Generated in ' . timer_stop(0, 2) . ' seconds. Used ' . round(memory_get_peak_usage()/1024/1024, 2) . ' MB memory.</div>';
    }

    public function removeDefaultWidgets() {
        unregister_widget('WP_Widget_Pages');
        unregister_widget('WP_Widget_Calendar');
        unregister_widget('WP_Widget_Archives');
        unregister_widget('WP_Widget_Links');
        unregister_widget('WP_Widget_Meta');
        unregister_widget('WP_Widget_Search');
        unregister_widget('WP_Widget_Text');
        unregister_widget('WP_Widget_Categories');
        unregister_widget('WP_Widget_Recent_Posts');
        unregister_widget('WP_Widget_Recent_Comments');
        unregister_widget('WP_Widget_RSS');
        unregister_widget('WP_Widget_Tag_Cloud');
        unregister_widget('WP_Widget_Custom_HTML');
    }

    public function autoRemoveFiles() {
        $files = ['readme.html', 'license.txt'];
        foreach ($files as $file) {
            $path = ABSPATH . $file;
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function pendingPostsNotice() {
        $pending = wp_count_posts()->pending;
        if ($pending > 0 && current_user_can('edit_posts')) {
            echo '<div class="notice notice-warning"><p>You have ' . $pending . ' pending posts.</p></div>';
        }
    }

    public function disableTaxonomyDropdown() {
        global $pagenow;
        if ($pagenow === 'post.php' || $pagenow === 'post-new.php') {
            echo '<style>.categorydiv div.tabs-panel { display: none; }</style>';
        }
    }

    public function showPendingCount($counts) {
        if (current_user_can('edit_others_posts')) {
            return $counts;
        }
        return $counts;
    }

    public function adminUpdateNotices() {
        if (!current_user_can('update_core')) {
            remove_action('admin_notices', 'update_nag', 3);
            remove_action('admin_notices', 'maintenance_nag', 10);
        }
    }

    public function changeExcerptMore() {
        return ' <a href="' . get_permalink() . '">Read more...</a>';
    }

    public function allowShortcodesInWidgets() {
        add_filter('widget_text', 'do_shortcode');
    }

    public function jqueryFromGoogle() {
        wp_deregister_script('jquery');
        wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js', false, '3.6.0');
        wp_enqueue_script('jquery');
    }

    public function removeAutoP($content) {
        if (is_singular()) {
            $content = str_replace('<p></p>', '', $content);
        }
        return $content;
    }

    public function allowWebp($mimes) {
        $mimes['webp'] = 'image/webp';
        return $mimes;
    }

    public function allowSvg($mimes) {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }

    public function disableBrowserCheck() {
        add_filter('wp_check_browser_version', '__return_false');
    }
}
