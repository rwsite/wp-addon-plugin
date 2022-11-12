<?php
/**
 * @author: Aleksey Tikhomirov
 */

function show_all_cats_custom_fileds(){
    $taxonomy = 'category';
    add_action( 'edit_category_form_fields', 'cat_fields' );
    function cat_fields(\WP_Term $term){
        global $taxonomy;
        $term_id = $term->term_id;
        echo '<pre>'; var_dump(get_term_meta( $term_id)); echo '</pre>';
    }
}