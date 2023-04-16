<?php
/**
 * Shortcode Table of contents
 *
 * @year: 2019-07-30
 */


function table_of_contents(){
    new TableOfContents();
}

require_once 'model/ShortcodeInterface.php';

final class TableOfContents implements ShortcodeInterface
{
    public $tag;
    public $title;
    public $description;
    public $icon;
    public $params;

    public function __construct( $tag = '', $title = null, $description = null, $icon = null )
    {
        $this->tag          = $tag ?: 'table_of_contents';
        $this->title        = __( 'Table of Contents', 'wp-addon' );
        $this->description  = __( 'Table of Contents plugin', 'wp-addon' );
        $this->icon         = file_exists( get_stylesheet_directory() . '/img/black_color.svg' ) ?
            get_stylesheet_directory_uri() . '/img/black_color.svg' :
            'https://cdn1.iconfinder.com/data/icons/sugar-glyph/64/174_sugar-white-cubes-512.png';

        add_action( 'init',         [$this, 'vc_support'] );
        // add_action( 'init',         [$this, 'pll_support']);

        if( !is_admin() ) {
            add_shortcode( $this->tag,          [$this, 'html']);
            add_action( 'wp_enqueue_scripts',   [$this, 'assets']);
        } else {
            add_action( 'admin_head',           [$this, 'tiny_mce_support']);
        }
    }


    /**
     * @param array $atts
     * @return string
     */
    public function html( $atts )
    {
        $this->params = shortcode_atts( [
            'title'     => apply_filters('toc_title', ''),
            'style'     => 'default',
            'headings'  => 'h2,h3,h4',
            'content'   => 'div.post-content',
            'table_color'=> ''
        ], $atts,  $this->tag );

        if(!empty( $this->params['title'])) {
            $this->params['title'] = esc_attr( $this->params['title'] );
        } else {
            $title = __('Contents','wp-addon'); // pll
            if ( function_exists('pll__') ){
                $title = pll__($title);
            }
            $this->params['title'] = $title;
        }

        if($this->params['content'] === 'div.post-content'){
            $this->params['content'] = '.post-item-single-container .post-content';
        }

        $this->params['table_color'] = $this->params['table_color'] ?: '';

        switch ($this->params['style']){
            case 'style1':
                wp_enqueue_style( 'toc.style1');
                break;
            case 'style2':
                wp_enqueue_style( 'toc.style2');
                break;
            default:
                wp_enqueue_style( 'toc.default');
                break;
        }
        add_action('wp_footer', [$this, 'front_script']);

        return '<!-- wp contents -->';
    }

    /**
     * WP_Bakery Page Builder support
     * @throws Exception
     */
    public function vc_support()
    {
        if ( !function_exists('vc_map')){
            return null;
        }

        vc_map(
            [
                'name'        => $this->title,
                'base'        => $this->tag,
                'description' => $this->description,
                'category'    => __('Elements', 'wp-addon' ),
                'icon'        => $this->icon,
                'params'      => [

                    [
                        'param_name'  => 'title',
                        'heading'     => __( 'Title', 'gillion' ),
                        'value'       => '',
                        'type'        => 'textfield',
                        'holder'      => 'div',
                        'class'       => '',
                    ],
                    [
                        'param_name' => 'style',
                        'heading'    => __( 'List style', 'gillion' ),
                        'value'      => [
                            esc_html__( 'Default', 'gillion' ) => 'default',
                            esc_html__( 'Style 1', 'gillion' ) => 'style1',
                            esc_html__( 'Style 2', 'gillion' ) => 'style2',
                            esc_html__( 'Style 3', 'gillion' ) => 'style3',
                        ],
                        'type'       => 'dropdown',
                        'class'      => '',
                        'group'      => __( 'Style', 'gillion' ),
                    ],
                    [
                        'param_name' => 'headings',
                        'heading'    => __( 'Order', 'gillion' ),
                        'value'      => [
                            esc_html__( 'h2, h3, h4', 'gillion' )  => 'h2,h3,h4',
                            esc_html__( 'Only h2', 'gillion' )  => 'h2',
                            esc_html__( 'Only h3', 'gillion' )  => 'h3',
                            esc_html__( 'Only h4', 'gillion' )  => 'h4',
                            esc_html__( 'All headers', 'gillion' )  => 'h1,h2,h3,h4,h5,h6',
                        ],
                        'type'       => 'dropdown',
                        'class'      => '',
                        'std'        => 'desc',
                    ],
                    [
                        'param_name'  => 'content',
                        'heading'     => __( 'Content for heading', 'gillion' ),
                        'description' => __( 'Content CSS class selector', 'gillion' ),
                        'value'       => 'div.post-content',
                        'type'        => 'textfield',
                    ],
                    [
                        'param_name'  => 'table_color',
                        'type'        => 'colorpicker',
                        'heading'     => __( 'List Color', 'gillion' ),
                        'description' => __( 'Table of Contents color', 'gillion' ),
                        'value'       => '',
                        'group'       => __( 'Style', 'gillion' ),
                    ],

                ],
            ]
        );

    }

    /**
     * Style and scripts support
     */
    public function assets()
    {
        wp_register_style( 'toc.style1',RW_PLUGIN_URL . 'assets/css/min/plugins/jquery.toc/style1.min.css');
        wp_register_style( 'toc.default',RW_PLUGIN_URL . 'assets/css/min/plugins/jquery.toc/default.min.css');

        if( defined( 'WP_DEBUG') && WP_DEBUG === true ) {
            wp_register_script( 'jquery.toc', RW_PLUGIN_URL . 'assets/js/plugins/jquery.toc/jquery.toc.js', ['jquery'], '1.0.3' );
        } else {
            wp_register_script( 'jquery.toc', RW_PLUGIN_URL . 'assets/js/plugins/jquery.toc/jquery.toc.min.js', ['jquery'], '1.0.3' );
        }
    }

    /**
     * Add tinyMCE button
     */
    public function tiny_mce_support(){
        if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' )) {
            return;
        }
        if ('true' === get_user_option( 'rich_editing' )) {
            add_filter( 'mce_buttons_3',          [$this, 'register_mce_button'] );
            add_filter( 'mce_external_plugins',  function( $plugins ) {
                $plugins[$this->tag] = RW_PLUGIN_URL . 'assets/js/tinymce/table_of_contents.js';
                return $plugins;
            });
        }
    }

    /**
     * Add php new tag support. TinyMCE JavaScript
     * @param $buttons array
     * @return array
     */
    public function register_mce_button($buttons){
        if(!isset($buttons[$this->tag])) {
            $buttons[$this->tag] = $this->tag;
        }
        return $buttons;
    }


    /**
     * Check, polylang is setup? After plugins loaded.
     * @unused - не используется
     * @return bool true -setup, false - no.
     */
    public function check_polylang(): bool
    {
        if ( ! function_exists( 'pll_the_languages' )) {
            return false;
        }
        return true;
    }

    /**
     * PolyLang support
     * @deprecated
     */
    public function pll_support(){
        $name   = 'Table of contents short-code title';
        $string = 'Contents';
        if(function_exists( 'pll_register_string')) {
            pll_register_string( $name, $string, 'Theme', false );
        }
    }

    public function admin_script($screen)
    {
        // TODO: Implement admin_script() method.
    }

    /**
     * Add front scripts and styles
     */
    public function front_script(){ ?>
        <?php if ( ! empty($this->params['table_color'])) : ?>
            <style>
                #toc li {
                    color: <?=esc_attr($this->params['table_color'])?> !important;
                }
            </style>
        <?php endif; ?>
        <script>
            (function ( $ ) {
                $.fn.TableOfContents = function( options ) {
                    var settings = $.extend({
                        duration:   "1000",
                        title:      "<?=$this->params['title']?>",
                        headings:   "h1, h2, h3, h4"
                    }, options );

                    return this.each(function() {
                        var article = $(this);
                        article.prepend('<div class="table-of-contents"><h3 id="toc-title" class="toc">' + settings.title + '</h3><ul></ul></div>');
                        var list = article.find('div.table-of-contents:first > ul:first');

                        article.find(settings.headings).each(function(){

                            if($(this).attr('id') === 'toc-title'){
                                return null;
                            }
                            $(this).removeAttr('id');

                            var heading = $(this);
                            var tag = heading[0].tagName.toLowerCase();
                            var title = heading.text();
                            var id = heading.attr('id');

                            if(typeof id === "undefined") {
                                id = Math.random().toString(36).substring(7);
                                heading.attr('id', id);
                            }
                            list.append('<li class="' + tag +'"><a href="#' + id + '" title="' + title + '">' + title + '</a></li>');
                        });

                        list.on('click', function(event){
                            var target = $(event.target);

                            if(target.is('a')){
                                event.preventDefault();
                                jQuery('html, body').animate({
                                    scrollTop: $(target.attr('href')).offset().top-100
                                }, settings.duration);
                                return false;
                            }
                        });
                    });
                };
            }( jQuery ));
            jQuery(document).ready(function() {
                jQuery('<?= esc_js($this->params['content']) ?>').TableOfContents();
            });
        </script>
        <?php
    }
}
