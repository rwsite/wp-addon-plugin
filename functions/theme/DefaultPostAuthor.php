<?php
/**
 * Plugin Name: Default Post Author
 * Description: Add Default post author settings for post publish metabox
 * Version:     1.0.0
 * Author:      Aleksey Tikhomirov
 * Author URI:  http://dataduck.com
 *
 * Requires at least: 4.6
 * Tested up to: 5.5
 * Requires PHP: 7.0+
 */

namespace theme;

use WP_Post;
use WP_User;

defined( 'ABSPATH' ) or die( 'Nothing here!' );

class DefaultPostAuthor
{
    public $key = 'users';

    /** @var WP_User */
    public $user;

    /** @var int */
    public $current_user_id;

    protected $ajax_key = 'user_select';
    protected $user_meta_key = 'default_author';

    public function __construct()
    {
        // user profile settings
        add_action( 'show_user_profile', 	[$this, 'show_user_settings']);
        add_action( 'edit_user_profile', 	[$this, 'show_user_settings']);

        // update user profile settings
        add_action( 'personal_options_update', [$this, 'save_user_profile_fields'] );
        add_action( 'edit_user_profile_update', [$this, 'save_user_profile_fields'] );

        // edit post page
        add_action( 'post_submitbox_misc_actions', [$this, 'post_author'] );
        add_action( 'admin_head', [$this, 'scripts']);

        // ajax action
        add_action( "wp_ajax_{$this->ajax_key}", [$this, 'ajax'] );

        // default post save action
        add_action( 'save_post', 	[$this, 'save_post_action'], 20, 3 );
    }

    /**
     * Show post author setting in Publish metabox
     * @param WP_Post $post
     */
    public function post_author( WP_Post $post){
        $this->user = get_user_by('ID', $post->post_author);
        $default_user_from_settings = (int) get_user_meta( get_current_user_id(), $this->user_meta_key, true);
        ?>
        <div class="misc-pub-section edit-post-author">
            <span class="dashicons dashicons-admin-users"></span>
            <?php
                if( 0 !== $default_user_from_settings && $default_user_from_settings !== $this->user->ID) {
                    $this->show_warning($default_user_from_settings);
                }
                echo $this->user->user_login;
            ?>
            <a id="p_author" href="javascript:void(0)">
                <span aria-hidden="true"><?= esc_html__('Edit', 'wp-addon') ?></span>
                <span class="screen-reader-text"><?= esc_html__('Edit status', 'wp-addon') ?></span>
            </a>

            <div id="p_author_select" class="wrapper" style="display: none">
                <?php echo $this->show_users(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Show warning
     * @param $user_id
     */
    public function show_warning($user_id){
        $user = WP_User::get_data_by('ID', $user_id);
        ?>
        <div id="message" class="notice notice-warning is-dismissible">
            <p><?php echo esc_html__('The author of the post will be changed to ', 'wp-addon') . $user->display_name; ?></p>
        </div>
        <?php
    }

    /**
     * Add js script for ajax
     */
    public function scripts(){
        global $post;
        ?>
        <style type="text/css">
            .edit-post-author span {
                color: #82878c;
            }
        </style>
        <script type="application/javascript">
            jQuery(document).ready(function( $ ) {
                $("#p_author").click(function(){
                    $( "#p_author_select").toggle("slow", function() {});
                });
                $(document).on('change', '.post_author', function() {
                    let selected = $(this).val();
                    let data = {
                        action: '<?php echo $this->ajax_key ?>',
                        user_value: selected,
                        post_id: '<?php echo $post->ID ?? get_the_ID() ?>',
                        current_user_id: '<?php echo get_current_user_id(); ?>'
                    };
                    $.post( ajaxurl, data, function(response) {
                        // alert(response);
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * Return user's list
     * @return string
     */
    public function show_users(){
        if( $cache = wp_cache_get( $this->key ) ) {
            return $cache;
        }

        $id = get_user_meta( get_current_user_id(), $this->user_meta_key, true);
        $args = array(
            'name' 				=> 'post_author_list',
            'selected' 			=> $id ?: $this->user->ID,
            'show_option_all' 	=> false,
            'show'              => 'display_name_with_login', // display_name || display_name_with_login
            'echo'              => false,
            'role__in'          => ['editor', 'author', 'administrator'],
            'class'             => 'post_author'
        );

        $list = wp_dropdown_users( $args );
        wp_cache_set( $this->key, $list ); // добавим данные в кэш
        return $list;
    }

    /**
     * Save new post author
     * @return void
     */
    public function ajax(){
        global $wpdb;
        $post_id = (int) $_POST['post_id'];
        $user_id = (int) $_POST['user_value'];
        $current_user_id = (int) $_POST['current_user_id'];

        //$new_post_author = \WP_User::get_data_by('ID', $user_id);
        //$post = WP_Post::get_instance($post_id);

        // update current user settings
        update_user_meta($current_user_id, $this->user_meta_key, $user_id);
        // update current post settings
        $r = $wpdb->update($wpdb->posts,['post_author' => $user_id],['id' =>$post_id]);
        if(is_int($r)){
            echo esc_html__('Successfully saved', 'wp-addon');
        }

        wp_die();
    }

    /**
     * Show default author in user settings
     * @param $user
     */
    public function show_user_settings( $user ) { ?>
        <table class="form-table">
            <tr>
                <th>
                    <label for="post_author_list"><?php _e( 'Default author', 'wp-addon' ); ?></label>
                </th>
                <td>
                    <?php
                    $selected_author =  get_the_author_meta( $this->user_meta_key, $user->ID );

                    $args = [
                        'name' 				=> 'post_author_list',
                        'id'                => 'post_author_list',
                        'selected' 			=> $selected_author,
                        'show_option_all' 	=> '  ',
                    ];
                    wp_dropdown_users( $args );
                    ?>
                    <span class="description">
						<?php _e( 'Select the default author, this will overwrite the global settings.', 'wp-addon' ); ?>
					</span>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Modify the post author, just before save the post
     *
     * @param int $post_id
     * @param WP_Post $post Post object.
     * @param bool $update Whether this is an existing post being updated or not.
     *
     * @return WP_Post
     */
    public function save_post_action( $post_id, $post, $update  )
    {
        $parent_id = wp_is_post_revision( $post_id );
        if ( $parent_id || 'post' !== $post->post_type || !isset($_POST['post_author_list']) ) {
            return $post;
        }

        $default_author = (int) wp_kses_post( $_POST['post_author_list'] );

        if( !empty($default_author) ){
            $post->post_author = $default_author;
        }
        
        remove_action( 'save_post', [$this, 'save_post_action'],20 );
        wp_update_post( array( 'ID' => $post_id, 'post_author' => $post->post_author ) );
        add_action( 'save_post', [$this, 'save_post_action'], 20, 3 );
    }

    /**
     * @param $user_id
     *
     * @return bool
     */
    public function save_user_profile_fields( $user_id ) {
        if ( !current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }
        update_user_meta( $user_id, $this->user_meta_key, $_POST['post_author_list'] );
    }
}

return new DefaultPostAuthor();