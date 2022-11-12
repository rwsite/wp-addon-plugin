<?php
/**
 *
 * @year: 2019-04-23
 * @see assets js "mce button"
 */


function add_bootstrap_3()
{
    class TinyBootstrapExtends
    {

        public $name;

        public function __construct()
        {
            $this->name = 'bootstrap';
            add_action('admin_head',            [$this, 'show']);
            add_filter('mce_css',               [$this, 'add_mce_css']);
            add_action('admin_footer',          [$this, 'get_shortcodes']);
        }


        public function show()
        {
            // check user permissions
            if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' )) {
                return;
            }

            if ('true' === get_user_option( 'rich_editing' )) { // check if WYSIWYG is enabled
                add_filter( 'mce_external_plugins', [$this, 'add_js_mce'], 10, 1 );
                add_filter( 'mce_buttons', [$this, 'register_mce_button'] );
            }
        }

        /**
         * Add JS
         * @param $plugin_array
         *
         * @return mixed
         */
        public function add_js_mce($plugin_array)
        {
            $arr['bootstrap'] = RW_PLUGIN_URL . 'assets/js/tinymce/bootstrap.js';

            //RW_PLUGIN_URL . 'assets/js/tinymce/mce-button.js';
            return $plugin_array + $arr;
        }

        /**
         * Register new button in the editor
         *
         * @param $buttons array
         *
         * @return array
         */
        public function register_mce_button($buttons): array
        {
            $buttons[] = $this->name;
            return $buttons;
        }

        /**
         * Show all shortcodes in JS
         * @unused
         */
        public function get_shortcodes()
        {
            global $shortcode_tags;
            echo '<script type="text/javascript">var shortcodes_button = new Array();';
            $count = 0;
            foreach ($shortcode_tags as $tag => $code) {
                echo "shortcodes_button[{$count}] = '{$tag}';";
                $count++;
            }
            echo '</script>';
        }

        /**
         * Add custom scripts to Editor
         *
         * @param $mce_css
         *
         * @return string
         */
        public function add_mce_css($mce_css): string
        {
            if ( ! empty( $mce_css )) {
                $mce_css .= ',';
            }
            $mce_css .= 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css';
            $mce_css .= ',';
            $mce_css .= '//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css';
            $mce_css .= ',';
            $mce_css .= RW_PLUGIN_URL . 'assets/css/min/tiny.min.css';
            $mce_css .= ',';
            return $mce_css;
        }

    }

    new TinyBootstrapExtends();

}