<?php
/**
 * Пример скрипта для смены автора всех постов
 *
 * @unused
 */

function fix_posts_author()
{
    function change_post_author()
    {
        $arg   = [
            'numberposts'      => -1,
            'category'         => 0,
            'orderby'          => 'date',
            'order'            => 'DESC',
            'include'          => [],
            'exclude'          => [],
            'meta_key'         => '',
            'meta_value'       => '',
            'lang'             => '', // polylang. no language specified
            'post_type'        => 'post',
            'suppress_filters' => true, // подавление работы фильтров изменения SQL запроса
        ];
        $posts = get_posts($arg);
        if ($posts) {
            foreach ($posts as $post) {
                if ($post->post_author == '23') {
                    continue;
                }
                $post->post_author = 23;
            }
            wp_reset_postdata();
        }
    }
    add_action('wp', 'change_post_author');
}