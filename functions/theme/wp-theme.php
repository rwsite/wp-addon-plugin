<?php

class ThemeFeatures
{
    public function add_actions(){

        add_filter( 'excerpt_length', function ($excerpt_length){
            return '20';
        } );

        # Читать далее
        add_filter('excerpt_more', function (){
            return '';
        }, 100);
        add_filter('wp_trim_excerpt', [$this, 'wp_trim_excerpt'], 99, 2);

        add_filter('comment_form_defaults', function ($defaults){
            $defaults['title_reply'] = '<span class="lnr lnr-bubble"></span> '. $defaults['title_reply'];
            return $defaults;
        });

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

    public function wp_trim_excerpt($text, $raw_excerpt)
    {
        if ( is_admin() || ! get_the_ID() ) {
            return $text;
        }

        $permalink = esc_url( get_permalink( (int) get_the_ID() ) ); // @phpstan-ignore-line -- post exists

        return $text . ' ... <p><a class="btn btn-outline-primary understrap-read-more-link" href="' . $permalink . '">' . __(
                'Read More...',
                'understrap'
            ) . '<span class="screen-reader-text"> from ' . get_the_title( get_the_ID() ) . '</span></a></p>';
    }

}

(new ThemeFeatures())->add_actions();