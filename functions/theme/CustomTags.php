<?php
/**
 * Functions add custom taxonomy for current theme
 */

namespace theme;

use WP_Error;
use WP_Term;

class CustomTags
{

    public function __construct()
    {
        add_shortcode('show_customtags', [$this, 'show']);

        add_action('init', [$this, 'register_taxonomy']);
        //add_action('init', [$this, 'remove_tags']);
        add_filter('get_the_tags', [__CLASS__, 'get_the_tags']);
    }


    /**
     * Fix error when request to simple tag. Use in feeds and some plugins and themes
     *
     * @return WP_Term[]|false|WP_Error
     */
    public static function get_the_tags($terms = null)
    {
        return get_the_terms(get_the_ID() ?? 0, 'customtags');
    }

    /**
     * Register taxonomy
     */
    public function register_taxonomy()
    {
        $result = register_taxonomy( 'customtags',
            ['post'],
            [
                'hierarchical'          => true,
                'labels'                => [
                    'name'                       => __( 'Hierarchical Tags', 'gillion' ),
                    'singular_name'              => __( 'Tag', 'gillion' ),
                    'search_items'               => __( 'Search tag', 'gillion' ),
                    'popular_items'              => __( 'Popular tags' ),
                    'all_items'                  => __( 'All tags', 'gillion' ),
                    'parent_item'                => null,
                    'parent_item_colon'          => null,
                    'edit_item'                  => __( 'Edit hierarchical tag', 'gillion' ),
                    'update_item'                => __( 'Update', 'gillion' ),
                    'add_new_item'               => __( 'Add new', 'gillion' ),
                    'new_item_name'              => __( 'Name of tag', 'gillion' ),
                    'separate_items_with_commas' => __( 'Separete with comma', 'gillion' ),
                    'add_or_remove_items'        => __( 'Add or Remove tag', 'gillion' ),
                    /*'choose_from_most_used' => __('Выбрать из наиболее часто используемых в-тег'),*/
                    'menu_name'                  => __( 'Hierarchical Tags', 'gillion' )
                ],
                'public'                => true,
                'show_in_nav_menus'     => true,
                'show_ui'               => true,
                'show_tagcloud'         => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var'             => true,
                'rewrite'               => [
                    /* настройки URL пермалинков */
                    'slug'         => 'tags', // ярлык
                    'hierarchical' => true // разрешить вложенность
                ],
            ]
        );
    }


    /**
     * Show custom tags
     */
    public function show()
    {
        $terms = get_the_terms( get_the_ID(), 'customtags' );

        if (empty( $terms ) || is_wp_error( $terms )) {
            return;
        }
        $classes = 'post-tags clearfix ';
        $classes .= 'post-share-class';
        ?>
        <div class="<?php esc_attr_e($classes);?>">
            <span class="terms-label"><i class="fa fa-tags"></i></span>
            <?php
            foreach ($terms as $term) {

                $link = get_term_link( $term, 'customtags' );
                if (is_wp_error( $link )) {
                    continue;
                }
                ?><a href="<?php echo esc_url( $link ); ?>"
                     rel="tag"><?php echo $term->name; ?></a>
                <?php
            }
            ?>
        </div>
        <?php
        unset( $terms );
    }

    /**
     * Unregister post_tag
     */
    public function remove_tags() {
        global $wp_taxonomies;
        $tax = 'post_tag';

        if( taxonomy_exists( $tax ) ) {
            unset( $wp_taxonomies[$tax] );
        }
    }

}

new CustomTags();