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

        add_shortcode('read_time', [$this, 'add_shortcode']);
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
        $wordsPerMinute = 150;

        $string = strip_tags($string);
        $wordCount = str_word_count($string);
        $minutesToRead = round($wordCount / $wordsPerMinute);

        if($minutesToRead < 1){// if the time is less than a minute
            return sprintf(esc_html__('%s min', 'theme'), 1);
        }

        return sprintf(esc_html__('%s min', 'theme'), $minutesToRead);
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