<?php

namespace yaexam\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * YAEXAM_Admin class.
 */
class YAEXAM_Admin {
	
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts'), 10 );
		add_action( 'delete_post', array( $this, 'delete_post') );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {

		include_once( 'class-yaexam-admin-notices.php' );
		include_once( 'class-yaexam-admin-menus.php' );
		include_once( 'class-yaexam-admin-post-types.php' );
		include_once( 'yaexam-admin-functions.php' );

		add_filter('mce_external_plugins', array($this, 'my_mce_external_plugins'));
	}

	public function my_mce_external_plugins($plugins) {   

	    $plugins['code'] = YAEXAM_URI . 'assets/js/tinymce/' . 'code-plugin.js';
	    return $plugins;
	}


	public function admin_enqueue_scripts() {

		global $post;

		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		
		$js_path	=	YAEXAM_URI . 'assets/js';
		
		wp_register_script( 'vue', $js_path . '/vue.js' );
		wp_register_script( 'yaexam-admin', $js_path . '/admin.js' );
		wp_register_style( 'yaexam-admin', YAEXAM_URI . 'assets/css/admin.css' );
		
		if ( in_array( $screen_id, array(
			'exam', 'edit-exam',
			'usergroup', 'edit-usergroup', 
			'yaexam', 'yaexam_page_em-settings', 
			'toplevel_page_yaexam',
			'exam_page_em-results',
			'yaexam_page_em-questions', 
			'yaexam_page_em-dashboard', 
			'yaexam_page_em-extensions', 
			'yaexam_page_em-importing-questions',
		) ) ) {

			wp_enqueue_script( 'wp-api' );
			wp_enqueue_script( 'yaexam-admin', array('jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-sortable', 'underscore', 'wp-api', 'vue'), '1.0.0');
			
			$ajax_nonce 		=	wp_create_nonce( "admin_yaexam" );

			$admin_js_params 	=	array(
				'plugin_url'	=>	YAEXAM_URI,
				'ajax_url'		=>	admin_url( 'admin-ajax.php' ),
				'admin_edit_url' => admin_url( 'post.php?action=edit' ),
				'admin_question_edit_url' => admin_url('admin.php?page=em-questions&tab=questions'),
				'security'		=>	$ajax_nonce,
				'trans'			=>	[
					'name'		=>	esc_html__('Name', 'yaexam'),
					'type'		=>	esc_html__('Type', 'yaexam'),
					'total'		=>	esc_html__('Total', 'yaexam'),
					'selected' 	=> esc_html__('Selected', 'yaexam'),
					'helper_confirm_1' => esc_html__('When you confirm the data will be saved, users playing this exam will be stop for saving!', 'yaexam')
				]
			);
			
			switch($screen->id){
				
				case 'exam':
					
					$admin_js_params['id'] = $post->ID;

				break;

				case 'plugins':

					

				break;

			}

			wp_localize_script( 'yaexam-admin', 'yaexam', $admin_js_params );

			wp_enqueue_style( 'boostrap', YAEXAM_URI . 'assets/css/bootstrap.min.css' );
			
			wp_enqueue_style( 'yaexam-google-fonts', 
				'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons' );

			wp_enqueue_style( 'yaexam-admin', ['yaexam-google-fonts'], '1.0.0' );
		}

		wp_enqueue_style( 'yaexam', YAEXAM_URI . 'assets/css/admin-all-min.css', '1.0.0' );
	}

	public function delete_post( $post_id ) {
		
		global $wpdb;

		if( get_post_type($post_id) == 'exam' ) {
			
			$wpdb->delete( $wpdb->prefix . 'yaexam_exam_results', ['exam_id' => $post_id], ['%d'] );
			$wpdb->delete( $wpdb->prefix . 'yaexam_save_later', ['exam_id' => $post_id], ['%d'] );
			$wpdb->delete( $wpdb->prefix . 'yaexam_user_questions', ['exam_id' => $post_id], ['%d'] );
			$wpdb->delete( $wpdb->prefix . 'yaexam_user_sessions', ['exam_id' => $post_id], ['%d'] );
			$wpdb->delete( $wpdb->prefix . 'yaexam_woo_exams', ['exam_id' => $post_id], ['%d'] );
			$wpdb->delete( $wpdb->prefix . 'yaexam_results', ['exam_id' => $post_id], ['%d'] );
			$wpdb->delete( $wpdb->prefix . 'yaexam_exam_questions', ['exam_id' => $post_id], ['%d'] );
		}
	}
}

return new YAEXAM_Admin();
