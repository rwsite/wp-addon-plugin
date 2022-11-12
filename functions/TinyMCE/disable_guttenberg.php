<?php
/**
 * @author: Aleksey Tikhomirov
 */

## Отключает новый редактор блоков в WordPress (Гутенберг).
## ver: 1.0
function disable_guttenberg()
{
    add_filter('use_block_editor_for_post_type', '__return_false', 100);
    remove_action('wp_enqueue_scripts', 'wp_common_block_scripts_and_styles');
    // Move the Privacy Policy help notice back under the title field.
    add_action('admin_init', function () {
        remove_action('admin_notices', ['WP_Privacy_Policy_Content', 'notice']);
        add_action('edit_form_after_title', ['WP_Privacy_Policy_Content', 'notice']);
    });
}