<?php
/**
 * Plugin Name: Wordpress Post views counter Plugin
 * Plugin URL: https://dataduck.com
 * Description: Wordpress post views counter plugin.
 * Version: 1.0.0
 * Text Domain: wp-addon
 * Domain Path: /languages
 * Author: Aleksey Tikhomirov
 *
 * Requires at least: 4.6
 * Tested up to: 5.3.3
 * Requires PHP: 7.0+
 *
 */

final class PostViews
{

    public $action = 'views_action';

    const KEY = 'views';

    public function __construct()
    {
        if ( ! is_admin() ) {
            add_action('init', [$this, 'add_view']);
        }

        add_action( 'wp_ajax_'        . $this->action,  [$this, 'update_post_views_count'] );
        add_action( 'wp_ajax_nopriv_' . $this->action,  [$this, 'update_post_views_count'] );
        add_action( 'wp_enqueue_scripts',               [$this, 'ajax_data'], 99 );
        add_action( 'post_submitbox_misc_actions',      [$this, 'show_likes'] );
        add_shortcode('most_viewed', [$this, 'get_most_viewed']);
    }

    public function add_view()
    {
        $settings = $this->get_views_settings();

        if ($settings === 'default') {
            add_action( 'wp_footer', [$this, 'add_post_views'], 99 );
        } elseif ($settings === 'ajax') {
            add_action( 'wp_footer', [$this, 'add_view_to_post'], 99 );
        }
    }

    public function show_likes(){
        $views = self::get_post_views(get_the_ID());
        ?>
        <div class="misc-pub-section edit-post-author">
            <span class="dashicons dashicons-visibility"></span> <?= sprintf( _n( '%s view', '%s views', $views ), $views ) ?>
        </div>
        <?php
    }

    /**
     * Get views settings - ajax or meta.
     *
     * @return string
     */
    public function get_views_settings(): string
    {
        if ( function_exists( 'fw_get_db_settings_option' ) ) {
            return get_option( 'post_view_mode', 'default' );
        }

        return 'default';
    }


    /**
     * Update Post Views Count via http request
     *
     * @return void
     */
    public function add_post_views()
    {
        global $post;
        // только для постов и cpt
        if ( ! empty( $post->ID ) && $post->ID > 0 && $this->views_filter( $post->ID )) {
            $count = (int) get_post_meta( $post->ID, self::KEY, true );
            if ( empty($count) ) {
                $count = 1;
            } else {
                ++$count;
            }
            update_post_meta( $post->ID, self::KEY, $count );
        }
    }


    /**
     * Get/Update Post Views Count
     * with AJAX
     */
    public function update_post_views_count()
    {
        $id = filter_var($_POST['post_id']);
        if ($id === false) {
            return;
        }

        if ( $this->views_filter($id) ) {
            $count = (int) get_post_meta( $id, self::KEY, true );
            if ( empty($count) ) {
                $count = 1;
            } else {
                $count++;
            }
            update_post_meta( $id, self::KEY, $count );
            echo $count; // return
        } else {
            echo $id;
        }

        // выход нужен для того, чтобы в ответе не было ничего лишнего, только то что возвращает функция
        wp_die();
    }


    /**
     * Set ajax action. Remove this function from template
     */
    public function add_view_to_post()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                let data = {
                    action:     <?php echo $this->action; ?>,
                    post_id:    <?php echo get_the_ID();  ?>,
                    async:      true
                };
                $.post( Ajax.url, data, function (response) {
                    //alert('Получено с сервера: ' + response);
                });
            });
        </script>
        <?php
    }

    /**
     * Передаем данные из php в js
     */
    public function ajax_data()
    {
        wp_localize_script(
            'jquery-migrate',
            'Ajax',
            [
                'url'   => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( self::KEY ),
            ]
        );
    }

    /**
     * Чьи просмотры будем считать
     *
     * @param $id
     *
     * @return bool
     */
    public function views_filter($id): bool
    {
        if ( get_post_status( $id ) === 'publish'
            && ! current_user_can( 'editor' )
            && ! current_user_can( 'administrator' )
            && is_singular( 'post' )
        ) {
            return true;
        }

        return false;
    }


    /**
     * Получить самые просматриваемые посты
     *
     * @param string | array $args
     * @see https://wp-kama.ru/id_101/funktsiya-vyivoda-zapisey-po-kolichestvu-prosmotrov.html
     *
     * @return bool|int|mixed|string
     */
    public static function get_most_viewed( $args = '' ){
        global $wpdb, $post;

        if(is_string( $args)) {
            parse_str( $args, $i );
        } elseif (is_array( $args)){
            $i = wp_parse_args( $args);
        }

        $num    = isset( $i['num'] ) ? (int)$i['num'] : 10;
        $key    = isset( $i['key'] ) ? sanitize_text_field( $i['key'] ) : self::KEY;
        $order  = isset( $i['order'] ) ? 'ASC' : 'DESC';
        $days   = isset( $i['days'] ) ? (int)$i['days'] : 0;
        $format = isset( $i['format'] ) ? stripslashes( $i['format'] ) : '';
        $cache  = isset( $i['cache'] );
        $transient_cache  = isset( $i['transient_cache'] );
        $echo   = isset( $i['echo'] ) ? (int)$i['echo'] : 1;
        $return_type = isset( $i['return_type'] ) ? sanitize_text_field($i['return_type']) : 'string';
        $lang  = function_exists( 'pll_current_language') ? pll_current_language() : null;

        $cache_key = (string) md5( __FUNCTION__ . serialize( $args ) . $lang );

        //получаем и отдаем кеш если он есть
        if($cache && $cache_out = wp_cache_get( $cache_key )) {
            if( $echo ) {
                return print($cache_out);
            }
            return $cache_out;
        }

        if( $transient_cache && $transient_cache_out = get_transient($cache_key) ){
            if( $echo ) {
                return print($transient_cache_out);
            }
            return $transient_cache_out;
        }

        $AND_days = '';
        if( $days ){
            $AND_days = "AND post_date > CURDATE() - INTERVAL $days DAY";
            if( strlen( $days ) == 4 ){
                $AND_days = "AND YEAR(post_date)=" . $days;
            }
        }

        $sql = "SELECT p.ID, p.post_title, p.post_date, p.guid, p.comment_count, (pm.meta_value+0) AS views
	FROM $wpdb->posts p
		LEFT JOIN $wpdb->postmeta pm ON (pm.post_id = p.ID)
	WHERE pm.meta_key = '$key' $AND_days
		AND p.post_type = 'post'
		AND p.post_status = 'publish'
	ORDER BY views $order LIMIT $num";
        $results = $wpdb->get_results( $sql );
        if( ! $results ){
            return false;
        }

        $out = '<ul class="most-viewed">';
        $x = '';
        $psts = array();
        preg_match( '!{date:(.*?)}!', $format, $date_m );

        foreach( $results as $pst ){

            if( isset($lang) ) {
                $post_id = pll_get_post( $pst->ID, $lang );
                $pst     = WP_Post::get_instance( $post_id );
                if( ! $pst instanceof WP_Post){
                    continue;
                }
            }

            if( !array_key_exists($pst->ID, $psts) ) {
                $psts[$pst->ID] = $pst;
            }

            $x = ( $x === 'li1' ) ? 'li2' : 'li1';

            if( isset($post->ID) && $pst->ID === $post->ID ) {
                $x .= ' current-item';
            }

            $Title    = $pst->post_title;
            $a1       = '<a href="' . get_permalink( $pst->ID ) . "\" title=\"{$pst->views} просмотров: $Title\">";
            $a2       = '</a>';
            $comments = $pst->comment_count;
            $views    = $pst->views;

            if( $format ){
                $date    = apply_filters( 'the_time', mysql2date( $date_m[ 1 ], $pst->post_date ) );
                $Sformat = str_replace( $date_m[ 0 ], $date, $format );
                $Sformat = str_replace( [ '{a}', '{title}', '{/a}', '{comments}', '{views}' ], [ $a1, $Title, $a2, $comments, $views, ], $Sformat );
            } else {
                $Sformat = $a1 . $Title . $a2;
            }

            $out .= "<li class=\"$x\">$Sformat</li>";
        }
        $out .= '</ul>';

        if(0 === $echo) {
            switch ($return_type) {
                case 'array':
                    $out = $psts;
                    break;
                case 'wp_query':
                    $query              = new WP_Query();
                    $query->posts       = $psts;
                    $query->post_count  = count($psts);
                    $out                = $query;
                    break;
                default :
                    break;
            }
        }

        if( $cache ) {
            wp_cache_add( $cache_key, $out );
        }

        if( $transient_cache ) {
            set_transient( $cache_key, $out, DAY_IN_SECONDS );
        }

        if( $echo ) {
            echo $out;
            return null;
        }

        return $out;
    }


    /**
     * Получить просмотры поста
     *
     * @param null $id
     * @return string
     */
    public static function get_post_views($id = null): string
    {
        if( !isset( $id) ){
            $id = get_the_ID();
        }

        $count = get_post_meta($id, self::KEY, true);
        return ( $count && $count > 0 ) ? $count : '0';
    }

}

new PostViews();