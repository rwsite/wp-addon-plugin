<?php
/**
 * Lightcase.js for all post images
 */


class Lightcase
{
    public function __construct()
    {
        add_action( 'wp_enqueue_scripts',   [$this, 'enqueue'] );
        add_action( 'wp_footer',            [$this, 'wp_footer'], 9999 );
    }

    /**
     * Добавляем lightcase всех изображений поста
     */
    public function wp_footer()
    { ?>
    <script type="text/javascript">
        jQuery(document).ready(function($){

             $("body.single-post .post-content img").each( function() {

                 let fullimg;
                 let myObject = $(this);
                 let parent = $(this).parent();

                 // Уже есть ссылка на картинку
                 if ($(this).parent() !== 'a') {

                     let attr = '';
                     let caption = $(this).next().clone();
                     let captionhtml = caption.html();

                     if( $(this).hasClass('emoji') ){
                         return;
                     }

                     if(caption.length > 0 && caption !== ''){
                         attr = 'data-caption="' + captionhtml + '"';
                     }

                     if( $(this).attr('srcset') !== undefined ) {
                         let srcset = $(this).attr('srcset');
                         fullimg = srcset.replace(/ .*/, '');
                     } else {
                         fullimg = $(this).attr('src');
                     }

                     parent.html('<a href="' + fullimg + '" data-rel="lightcase" '+ attr +'>' + myObject.prop('outerHTML') + '</a>').append(caption);

                 } else if ($(this).parent() === 'a') {
                     parent.attr("data-rel", "lightcase"); // если ссылка на картинку уже есть добавляем поддержку Lightcase.js
                 }
             } );

             $('a[data-rel^=lightcase]').lightcase({
                 maxWidth: '1900',
                 maxHeight: '1600',
                 inline: {
                     width : '1900',
                     height : '1600'
                 },
                 typeMapping: {
                     'image': 'webp, jpg, jpeg, gif, png, bmp',
                 },
             });

         });
    </script>
    <?php
    }


    public function enqueue()
    {
        $theme   = wp_get_theme();
        $ver     = defined('WP_DEBUG_SCRIPT') ? current_time('timestamp') : $theme->get( 'Version' );
        $dir_uri = get_template_directory_uri();

        wp_enqueue_script( 'lightcase', $dir_uri . '/assets/js/plugins/lightcase.js', ['jquery', 'theme'], $ver, false);
        wp_enqueue_style( 'lightcase', $dir_uri . '/assets/css/plugins/lightcase.min.css', 'theme', $ver);
    }

}
