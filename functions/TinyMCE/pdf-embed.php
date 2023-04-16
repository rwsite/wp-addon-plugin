<?php



class PDF_embed
{
    public static $instance = 0;

    public $settings;

    public $name;
    private $icon;
    private $js;

    public function __construct()
    {
        $this->name = 'pdf';
        $this->icon = RW_PLUGIN_URL . 'assets/images/pdf-file.svg';
        $this->js   = RW_PLUGIN_URL . 'assets/js/tinymce/pdf-embed.js';

        $this->settings = 'native';

        if(1 !== ++self::$instance) {
            return;
        }

        // add_action('admin_head',   [$this, 'show']);
        // add_filter('mce_css',      [$this, 'add_mce_css']);
        // add_action('admin_footer', [$this, 'custom_js']);

        add_filter( 'media_send_to_editor', function ($html, $send_id, $attachment){

            $post = get_post( $send_id );

            if ( 'application/pdf' !== $post->post_mime_type ) {
                return $html;
            }

            if('native' == $this->settings) {
                return '<object data="' . $post->guid . '" type="application/pdf" width="100%" height="500px">' .
                    '<div class="pdf-js">Download the PDF to view it: <a href="' . $post->guid . '" target="_blank" class="pdf">Download PDF</a></div>'.
                    '</object>';
            }

        }, 10, 3);

        add_action('wp_footer', [$this, 'front']);
        add_action('wp_head',   [$this, 'front_js']);
    }


    public function show()
    {
        // check user permissions, check if WYSIWYG is enabled
        if ( ! current_user_can( 'edit_posts' ) || 'true' != get_user_option( 'rich_editing' )) {
            return;
        }

        // localize variable
        add_action( 'admin_print_footer_scripts', function () {
            wp_localize_script( 'wp-tinymce', 'pdficon',  [$this->icon] );
        });

        // add JS plugins to tinyMCE
        add_filter( 'mce_external_plugins', function($plugin_array){
            $arr[$this->name] = $this->js;
            $plugin_array = $plugin_array + $arr;
            return $plugin_array;
        }, 20, 1 );

        // add button to panel tinyMCE editor
        add_filter( 'mce_buttons_3', function ($buttons){
            return array_merge($buttons, [$this->name]);
        },20,1);
    }


    /**
     * Show all shortcodes in JS
     * @unused
     */
    public function custom_js()
    {
        global $shortcode_tags; ?>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                var $ = jQuery;

                if ($('.mce-insert-media button').length > 0) {
                    if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
                        $('.mce-insert-media button').on('click', function(e) {

                            e.preventDefault();
                            var button = $(this);
                            var id = button.prev();
                            wp.media.editor.send.attachment = function(props, attachment) {
                                id.val(attachment.id);
                            };
                            wp.media.editor.open(button);
                            return false;
                        });
                    }
                }
            });
        </script>
        <?php
    }

    /**
     * Add custom scripts to Editor
     *
     * @param $mce_css
     * @return string
     */
    public function add_mce_css($mce_css): string
    {
        return $mce_css;
    }


    public function front_js(){
        wp_enqueue_script('pdf-embed', RW_PLUGIN_URL . 'function/assets/js/viewer.js');
    }

    public function front(){
        ?>
        <script id="pdf-embed">
            console.log('pdf-embed');

            let url = jQuery('object[type="application/pdf"]').attr('data');
            let canvas = jQuery('object[type="application/pdf"]').find('.pdf-js');
            jQuery(canvas).html('');

            // Asynchronous download PDF
            //
            var loadingTask = pdfjsLib.getDocument(url);
            loadingTask.promise.then(function(pdf) {
                //
                // Fetch the first page
                //
                pdf.getPage(1).then(function(page) {
                    var scale = 1.5;
                    var viewport = page.getViewport({ scale: scale, });

                    //
                    // Prepare canvas using PDF page dimensions
                    //
                    var context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    var renderContext = {
                        canvasContext: context,
                        viewport: viewport,
                    };
                    page.render(renderContext);
                });
            });
        </script>
        <?php
    }
}
