<?php

namespace theme;

use WP_Post;

/**
 * Отображение всех метаполей поста
 *
 * @author: Aleksey Tikhomirov <a.tihomirov@dataduck.com>
 * @year  : 05.02.2020
 */
class PostMetaHelper
{
    public function __construct()
    {
        $this->add_actions();
    }

    public function add_actions()
    {
        add_action('admin_head', [$this, 'add_tab']);
    }

    public function add_tab()
    {
        $screen = get_current_screen();

        global $post;
        if (!isset($post) || !$post instanceof WP_Post) {
            return;
        }

        $meta = get_post_meta($post->ID);
        $meta = json_encode($meta, JSON_UNESCAPED_UNICODE);
        ob_start();
        echo '<textarea rows="20" cols="100">';
        var_dump(json_decode($meta, true));
        echo '</textarea>';
        $html = ob_get_clean();

        // Массив параметров для первой вкладки
        $args = [
            'title'    => 'Все мета-данные поста',
            'id'       => 'tab_3',
            'content'  => $html,
            'priority' => 30
        ];
        // Добавляем вкладку
        $screen->remove_help_tabs();
        $screen->add_help_tab($args);
    }
}

new PostMetaHelper();