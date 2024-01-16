<?php

class ThemeFeatures
{
    public function add_actions(){

       /* add_filter( 'excerpt_length', function ($excerpt_length){
            return '20';
        } );

        # Читать далее
        add_filter('excerpt_more', function (){
            return '';
        }, 100);*/

        add_filter('get_the_excerpt', [$this, 'wp_trim_excerpt'], 99, 2);


        // 0 to all image size
        add_action('after_setup_theme', function (){
            if(0 != get_option('medium_large_size_w')) {
                update_option('medium_large_size_w', 0);
            }
            # Отменим `-scaled` размер - ограничение максимального размера картинки
            add_filter( 'big_image_size_threshold', '__return_zero' );
            // 2x medium_large size.
            remove_image_size('1536x1536');
            // 2x large size.
            remove_image_size('2048x2048');
        });
    }

    public function wp_trim_excerpt($excerpt, $post)
    {
        if ( is_admin() || ! get_the_ID() ) {
            return $excerpt;
        }

        $excerpt_max_length = 180;

        //$excerpt = $post->post_excerpt;
        $excerpt = strip_shortcodes($excerpt);
        $excerpt = wp_strip_all_tags($excerpt);

        if( ($excerpt_max_length + 1) < mb_strlen($excerpt, 'UTF-8') ){
            $excerpt = preg_replace( '( [.*?])','',$excerpt);
            $excerpt = mb_substr($excerpt, 0, $excerpt_max_length, 'UTF-8');
            $excerpt = mb_substr($excerpt, 0, strripos($excerpt, ' ' ), 'UTF-8');
            $excerpt = trim(preg_replace( '/\s+/', ' ', $excerpt));
            $excerpt .= '...';
        }

        $permalink = esc_url( get_permalink( (int) get_the_ID() ) );
        return $excerpt . ' <p><a class="btn btn-outline-primary understrap-read-more-link" href="' . $permalink . '">' . __(
                'Read More...',
                'understrap'
            ) . '<span class="screen-reader-text"> from ' . get_the_title( get_the_ID() ) . '</span></a></p>';
    }

}

(new ThemeFeatures())->add_actions();