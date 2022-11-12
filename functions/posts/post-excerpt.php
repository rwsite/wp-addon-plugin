<?php
/**
 * @year: 2019-04-19
 */

if(!function_exists('change_excerpt')):
function change_excerpt()
{
    function change_excerpt_length($excerpt, $post)
    {
        $excerpt = preg_replace( ' ([.*?])','',$excerpt);
        $excerpt = strip_shortcodes($excerpt);
        $excerpt = strip_tags($excerpt);
        $excerpt = substr($excerpt, 0, 180);
        $excerpt = substr($excerpt, 0, strripos($excerpt, ' ' ));
        $excerpt = trim(preg_replace( '/\s+/', ' ', $excerpt));
        $excerpt .= '...';
        return $excerpt;
    }

    add_filter('get_the_excerpt', 'change_excerpt_length', 10, 2);
}
endif;