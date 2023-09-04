<?php
/**
 * Plugin Name: Wordpress post read time plugin
 * Plugin URL: https://rwsite.ru
 * Description: Wordpress post read time plugin.
 * Version: 1.0.0
 * Text Domain: theme
 * Domain Path: /languages
 * Author: Aleksey Tikhomirov
 *
 * Requires at least: 4.6
 * Tested up to: 5.3.3
 * Requires PHP: 7.0+
 *
 */

namespace theme;

class PostReadTime
{
    public static $inst = 0;
    public function __construct()
    {
        self::$inst++;
        $this->add_actions();
    }
    public function add_actions(){

        if(self::$inst !== 1) {
            return;
        }

        add_action('init', function () {

            if (!get_theme_support('read_time')) {
                return;
            }

            add_shortcode('read_time', [$this, 'add_shortcode']);
        });
    }

    public function add_shortcode($args = null){
        $args = wp_parse_args($args, [
            'icon' => true
        ]);

        if($args['icon']){
            // add icon to html
        }

        return self::get_read_time();
    }

    public static function get_read_time($post_id = null, $string = null )
    {
        $post = get_post($post_id ?? get_the_ID());
        $string = !empty($string) ? $string : $post->post_content; // без фильтров, что бы сделать работы быстрее

        $string = strip_tags($string);
        $string = preg_replace('/\s+/', ' ', trim($string));
        $words = explode(" ", $string);
        $words = count($words);

        $min = floor($words / 200);
        $sec = floor($words % 200 / (200 / 60));
        if ($min < 1) {
            return sprintf(esc_html__('%s min', 'theme'), 1);
        }
        if ($sec >= 20) {
            $min++;
        }
        return sprintf(esc_html__('%s min', 'theme'), $min);
    }
}

/**
 * Get post read time
 *
 * @param string|null $post_content
 * @return string
 */
function get_post_read_time($post_content = null)
{
    return PostReadTime::get_read_time();
}

new PostReadTime();