<?php

/**
 * @param int|WP_Post $post
 * @return int
 */
function get_post_views($post = null)
{
    $post = get_post($post);
    $post = $post instanceof WP_Post ? $post->ID : get_the_ID();
    return get_post_meta($post,'views', true) ?: 0;
}

/**
 * @param int|WP_Post $post
 * @return string
 */
function get_post_subtitle($post = null)
{
    $post = get_post($post);
    $post = $post instanceof WP_Post ? $post->ID : get_the_ID();
    return get_post_meta($post,'subtitle', true);
}

function the_subtitle($before, $after)
{
    $string = get_post_subtitle();
    if($string) {
        echo $before . $string . $after;
    }
}


/**
 *  Get thumbnail
 */
if(!function_exists('thumbnail')):
function thumbnail($width = '150', $height = '150', $crop = true, $post_id = null, $show_placeholder = true)
{
    $thumb = null;
    if($show_placeholder) {
        $thumb = get_option('kama_thumbnail', ['no_photo_url' => RW_PLUGIN_URL . 'assets/images/no-photo.webp'])['no_photo_url'];
    }

    $attachments = get_children([
        'post_parent'    => $post_id ?? get_the_ID(),
        'post_mime_type' => 'image',
        'post_type'      => 'attachment',
        'numberposts'    => 1,
        'order'          => 'DESC',
    ]);

    $attach = array_shift($attachments);
    if (function_exists( 'kama_thumb_img' ) && $show_placeholder) {
        $thumb = kama_thumb_img( ['width' => $width, 'height' => $height, 'crop' => $crop, 'class' => 'rounded', 'stub_url' => $thumb], $attach->ID ?? 'notset' );
    } else {
        $thumb = isset($attach) ? wp_get_attachment_image( $attach->ID, [$width, $height], true ) : null;
    }

    return $thumb;
}
endif;