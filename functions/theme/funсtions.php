<?php

/**
 * @param int|WP_Post $post
 *
 * @return int
 */
function get_post_views($post = null)
{
    $post = get_post($post);
    $post = $post instanceof WP_Post ? $post->ID : get_the_ID();
    return get_post_meta($post, 'views', true) ?: 0;
}

function get_post_read_time($post = null)
{
    return \theme\PostReadTime::get_read_time($post );
}

/**
 * @param int|WP_Post $post
 *
 * @return string
 */
function get_post_subtitle($post = null)
{
    return \theme\PostExtendSubtitle::get_post_subtitle();
}

function the_subtitle($before, $after)
{
    return \theme\PostExtendSubtitle::the_subtitle($before, $after);
}


function get_post_thumbnail($attr = null)
{
    $thumbnail = null;
    $attr = wp_parse_args($attr, [
        'width'            => '480',
        'height'           => '340',
        'crop'             => true,
        'post_id'          => null,
        'show_placeholder' => true,
        'attach_id'        => null,
        'class'            => 'img-fluid rounded',
        'attr'             => ''
    ]);
    extract($attr);

    if ($show_placeholder) {
        $thumbnail = get_option('kama_thumbnail', ['no_photo_url' => RW_PLUGIN_URL . '/wp-content/uploads/2023/08/no_img.jpg'])['no_photo_url'];
    }

    $attachments = get_children([
        'post_parent'    => $post_id ?: get_the_ID(),
        'post_mime_type' => 'image',
        'post_type'      => 'attachment',
        'numberposts'    => 1,
        'order'          => 'DESC',
    ]);

    $attach_id = get_post_thumbnail_id($post_id ?? get_the_ID());
    if (empty($attach_id) && !empty($attachments)) {
        $attach_id = array_values($attachments)[0]->ID;
    }

    if (function_exists('kama_thumb_img')) {
        $thumbnail = kama_thumb_img([
            'width'     => $width,
            'height'    => $height,
            'crop'      => $crop,
            'class'     => $class,
            'stub_url'  => $show_placeholder ? $thumbnail : '',
            'attach_id' => $attach_id ?: null
        ]);
    } else {
        $thumbnail = !empty($attach_id) ? wp_get_attachment_image($attach_id, [$width, $height], true) : null;
    }

    return $thumbnail;
}