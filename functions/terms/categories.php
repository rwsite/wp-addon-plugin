<?php
/**
 * @author: Aleksey Tikhomirov
 */

function show_all_custom_fields(){
    $taxonomies = ['category','post_tag'];
    foreach ($taxonomies as $taxonomy) {
        // global $taxonomy;
        add_action("{$taxonomy}_term_edit_form_top", function (WP_Term $term) {
            $term_id = $term->term_id;
            echo '<pre>';
            var_dump(get_term_meta($term_id));
            echo '</pre>';
        }, 10, 1);
    }
}