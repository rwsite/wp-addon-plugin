<?php
/**
 * CategoriesFilter
 *
 */


final class CategoriesFilter
{
    private static $instance;

    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function __clone(){}
    public function __wakeup(){}

    /**
     * CategoriesFilter constructor.
     */
    private function __construct()
    {
        add_action('wp',
            [$this, 'wp_cat_filter']); // редирект на главную, если человек попал в запрещенную категорию
        add_filter('get_next_post_excluded_terms',
            [$this, 'exclude_cat'], 10, 1);// nex_post
        add_filter('get_previous_post_excluded_terms',
            [$this, 'exclude_cat'], 10, 1);// prev post
        add_action( 'pre_get_posts',
            [$this, 'filter_run'], 10 ); // Убираем категории по ID с фронта
    }

    /**
     * @return string | null
     */
    private function get_settings(){
        $options = ControllerWP::get_settings();
        $cat_ids = $options['posts']['exclude_cat_val'];// получаем список категорий
        return $cat_ids ?? null;
    }

    /**
     * @param WP_Query $query
     */
    public function filter_run( \WP_Query $query)
    {
        if (is_admin()) {
            return;
        }

        $cat_ids = $this->get_settings();
        if ( isset($cat_ids) && !empty( $cat_ids) ) {
            str_replace( ', ', ', -', $cat_ids);
            $cats = explode( ', ', $cat_ids );
            $query->set( 'category__not_in',  $cats);
        }
    }

    /**
     * @param array $excluded_terms
     *
     * @return array | null
     */
    public function exclude_cat($excluded_terms = null)
    {
        $cats = explode( ', ', $this->get_settings() );
        if ( ! empty( $cats ) ) {
            $excluded_terms = $cats;
        }
        return apply_filters('exclude_categories_filter', $excluded_terms );
    }

    /**
     * Redirect from exclude category to home page
     */
    public function wp_cat_filter(){
        global $wp_query;
        $cat_ids = $this->exclude_cat();
        if($wp_query->is_archive &&
           $wp_query->is_category &&
           in_array( get_queried_object()->term_id, $cat_ids, false)
            ){
                wp_redirect( get_home_url(), 301, 'wp-addon');
        }
    }
}

if (!function_exists( 'exclude_cat')) :
function exclude_cat()
{
    CategoriesFilter::getInstance();
}
endif;