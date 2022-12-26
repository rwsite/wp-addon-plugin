<?php
/**
 * Debug
 *
 * @param $data
 * @return void
 */

if(!function_exists('debug')):
function debug($data, $mode = 'log'){

	global $wp_query;
	$wp_query->debug_data = $data;

	switch ($mode){
		case 'console':
			add_action('wp_head', 'console');
			add_action('admin_head', 'console');
			break;
		case 'log':
			error_log(print_r($wp_query->debug_data, true));
			break;
		default:
			global $wp_query;
			echo '<pre>';
			print_r($data ?? $wp_query->debug_data);
			echo '</pre>';
		break;
	}
}
endif;

if(!function_exists('console_log')):
function console_log($data = null){
    debug($data, 'log');
    console($data);
}
endif;

if(!function_exists('console')):
function console($data = null){
	global $wp_query;

	$json_data = addcslashes(json_encode($data ?? $wp_query->debug_data ?? '', JSON_FORCE_OBJECT),'\'\\');
	?>
	<script>
        console.log(JSON.parse('<?= $json_data; ?>'));
	</script>
	<?php
}
endif;