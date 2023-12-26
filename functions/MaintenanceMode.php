<?php
/**
 * Maintenance Mode. Тех работы.
 */

final class MaintenanceMode {

    public function __construct()
    {
        add_action('get_header', [$this, 'maintenance_mode']);
    }
    
    public function maintenance_mode() {
        if ( !current_user_can( 'edit_themes' ) || !is_user_logged_in() ) {

	       add_action('wp_enqueue_scripts', function (){
		       wp_enqueue_style('dashicons');
	       });

           if(empty($template = $this->get_template())) {
	           do_action( 'wp_head' );
			   ?>
               <div>
	               <h1 class="text-center">
		               <span class="dashicons dashicons-admin-tools" style="font-size: 100px; width: 100%; height: 120px;"></span>
		               <span><?= __( 'Технические работы.', 'wp-addon' ) ?></span>
                    </h1>
	               <p class="text-center">
		               <?= __( 'Извините, в настоящий момент на сайте проводятся технические работы.', 'wp-addon' ) ?><br>
		               <?= __( 'Попробуйте зайти позднее.', 'wp-addon' ) ?></p>
	               </p>
               </div>
			   <?php
           } else {
               echo $template;
           }

           wp_die();
        }
    }

    public function get_template(){
        return $html ?? '';
    }
}

if(!function_exists( 'enable_maintenance')):
function enable_maintenance(){
    return new MaintenanceMode();
}
endif;