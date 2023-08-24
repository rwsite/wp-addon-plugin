<?php
/**
 * @author: Aleksey Tikhomirov
 * @year: 2019-12-10
 */

namespace theme;

use WP_Post;

class PostExtendSubtitle
{
    protected $post;
    protected $post_id;

    public static $inst = 0;

    public static $prefix = '_page';

    public function __construct()
    {
        self::$inst++;
        $this->add_actions();
    }

    public function add_actions(){

        if(self::$inst !== 1) {
            return;
        }

        add_filter( 'hidden_meta_boxes',    [$this, 'default_metabox_settings'], 10, 3 );
        add_action( 'edit_form_after_title',[$this, 'edit_subtitle'], 10, 1 );

        // обновление полей при сохранении
        add_action( 'save_post',            [$this, 'post_fields_update'], 10, 3 );

/*        add_action( 'admin_init', function() {
            $post_id = $_GET['post'] ?? $_POST['post_id'] ?? $_POST['post_ID'] ?? null ;
            if( !isset( $post_id ) ){ return; }
            remove_post_type_support('page', 'revisions');
            remove_post_type_support('page', 'trackbacks');
        });*/
    }

    /**
     * Showing the Subtitle on post edit page
     *
     * @param WP_Post $post
     */
    public function edit_subtitle(WP_Post $post){
        $screen = get_current_screen();
        if(!isset($screen) || $screen->post_type !== 'post'){
            return;
        }
        $subtitle = get_post_subtitle($post->ID);
        ?>
        <p>
            <label>
                <input type="text" name="post_data[subtitle]" value="<?php echo $subtitle ?>" style="width:50%" placeholder="<?php echo esc_html__('Post subtitle', 'gillion')?>"/>
            </label>
        </p>
        <p>
            <textarea name="post_data[post_excerpt]" style="width:100%;height:50px;" placeholder="<?php echo esc_html__('Post excerpt', 'gillion')?>"><?php echo $post->post_excerpt ?></textarea>
        </p>
        <input type="hidden" name="post_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>" />
        <?php
    }


    /**
     * Сохраняем данные, при сохранении поста
     *
     * @param int $post_id
     * @param WP_Post $post
     * @param bool $update
     *
     * @return bool|int
     */
    public function post_fields_update( $post_id, $post, $update )
    {
        // базовая проверка
        if (
            !isset( $_POST['post_data'], $_POST['ID'] )
            || empty( $_POST['post_data'] )
            || empty( $_POST['ID'] )
            || ! wp_verify_nonce( $_POST['post_nonce'], __FILE__ )
            || wp_is_post_autosave( $post_id )
            || wp_is_post_revision( $post_id )
        ) {
            return false;
        }

        $this->post_id = $post_id;
        $this->post = $post;

        $_POST['post_data'] = array_map( 'sanitize_text_field', $_POST['post_data'] ); // чистим все данные от пробелов по краям

        $meta_data = $this->setup_utm_meta();

        foreach( $meta_data as $key => $value ){
            if( empty($value) ){
                delete_post_meta( $post_id, $key ); // удаляем поле если значение пустое
                continue;
            }

            if($key === 'post_excerpt'){
                $post->post_excerpt = stripslashes_deep($value); // $value
                remove_action( 'save_post', [$this, 'post_fields_update'], 10  );
                wp_update_post( $post );
                add_action( 'save_post', [$this, 'post_fields_update'], 10, 3 );
            } else {
                update_post_meta( $post_id, $key, $value );
            }
        }

        return $post_id;
    }

    /**
     * Set utm meta
     *
     * @return array
     */
    private function setup_utm_meta()
    {
        return $_POST['post_data'] ;
    }


    /**
     * @param $hidden
     * @param $screen
     * @param $use_defaults
     *
     * @return array
     */
    public function default_metabox_settings($hidden, $screen, $use_defaults){
        unset( $hidden);
        $hidden = array();
        if ( 'post' === $screen->base && 'post' === $screen->post_type  ) {
            $hidden = array( 'slugdiv', 'trackbacksdiv', 'postcustom', 'postexcerpt', 'commentstatusdiv', 'commentsdiv', 'authordiv', 'revisionsdiv' );
        }

        return $hidden;
    }
}

new PostExtendSubtitle();