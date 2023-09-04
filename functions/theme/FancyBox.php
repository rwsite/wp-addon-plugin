<?php
/**
 * FancyBox for post images.Скрипт для увеличения картинок в посте
 *
 * @author: Aleksey Tikhomirov
 * @year: 2019-06-25
 */

namespace theme;

class FancyBox{

    /**
     * @var false|mixed|null
     */
    public array $settings;

    public function __construct()
    {
        $this->settings = $this->get_settings();
    }

    public function get_settings()
    {
        return get_option('fancy_box', ['src' => 'cloud', 'selector' => '.single-post article .entry-content img, .entry-header img']);
    }

    public function add_actions(){
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue'] );
        add_action('wp_footer', [$this, 'footer']);
    }

    public function enqueue(){
        if(is_admin()){
            return;
        }

        if('cloud' === $this->settings['src']) {
            wp_enqueue_script('jquery.fancybox', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js', 'jquery', '5.0', []);
            wp_enqueue_style('jquery.fancybox', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css');
        } else {
            //wp_enqueue_script( 'scripts', get_stylesheet_directory_uri() . '/assets/js/scripts.js','jquery', false,true);
            //wp_enqueue_style( 'jquery.fancybox', get_stylesheet_directory_uri() . '/assets/css/custom.min.css' );
        }
    }

    public function footer()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                console.log('WP FancyBox included');
                $("<?= esc_attr($this->settings['selector']); ?>").each( function() {

                    let myObject = $(this);
                    let parent = $(this).parent();

                    if ($(this).parent() !== 'a') {
                        if( $(this).attr('srcset') !== undefined ) {
                            var srcset = $(this).attr('srcset');
                            var fullimg = srcset.replace(/ .*/, '');
                            myObject.replaceWith('<a data-src="' + fullimg + '" data-fancybox="images">' + myObject.prop('outerHTML') + '</a>');
                        } else {
                            myObject.replaceWith('<a data-src="' + $(this).attr('src') + '" data-fancybox="images">' + myObject.prop('outerHTML') + '</a>');
                        }

                    } else if ($(this).parent() === 'a') {
                        parent.attr("data-fancybox", "images").removeAttr("href");
                    }
                } );

                Fancybox.bind('[data-fancybox="images"], [data-fancybox="gallery"]', {});

            });
        </script>
        <?php
    }


    public function get_header_image_tag($html, $header, $attr){
       return $html;
    }
}

(new FancyBox())->add_actions();