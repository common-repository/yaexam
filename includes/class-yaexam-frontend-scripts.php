<?php 

namespace YaExam;

use YaExam\YAEXAM_Exam;

defined( 'ABSPATH' ) || exit;

class YAEXAM_Frontend_Scripts {
	
	public static function init() {

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_styles' ) );
		
	}

	public static function load_styles()
	{

		$min_js	=	yaexam_is_debug() ? '' : '.min';

		wp_enqueue_style( 'yaexam-google-fonts', '//fonts.googleapis.com/css?family=Material+Icons' );
		wp_enqueue_style( 'yaexam-boostrap', YAEXAM_URI . 'assets/css/bootstrap.min.css', [], '4.5.0' );
		wp_enqueue_style( 'yaexam-frontend', YAEXAM_URI . 'assets/css/front.css', array('yaexam-google-fonts', 'yaexam-boostrap'), '1.0.0' );

	}
	
	public static function load_scripts()
	{
		
		global $post;
		
		if ( ! did_action( 'before_yaexam_init' ) || !is_yaexam() ) {
			return;
		}
		
		$min_js	=	yaexam_is_debug() ? '' : '.min';

		$ajax_nonce 		=	wp_create_nonce( "frontend_yaexam" );
			
		$js_path		=	YAEXAM_URI . 'assets/js';

		$js_params 	=	array(
			'plugin_url'	=>	YAEXAM_URI,
			'site_url'		=>	is_ssl() ? home_url('/', 'https') : home_url(),
			'security'		=>	$ajax_nonce,
		);

		if( yaexam_is_doing() ) {

			$sid = isset($_GET['em-doing']) ? yaexam_clean($_GET['em-doing']) : false;

			$session    	= 	yaexam_get_user_session($sid);
			
			$exam			=	new YAEXAM_Exam($post->ID);

			$settings		=	$exam->get_settings();

			$js_params['id']  = $post->ID;
			$js_params['sid'] = $sid;
			
			$js_params['passed_time'] = 0;
			$js_params['exam_url'] = get_permalink( $post->ID );

			$js_params['can_play'] = yaexam_can_do_exam( $post->ID );

			$js_params['doing_after_form'] = apply_filters('yaexam_doing_after_form', [], $post->ID);
			
			if( $session ) {
				
				if( $settings['duration'] > 0 ){
				
					$js_params['passed_time']	=	time() - $session['time_started'];
				}

			} 

		}

		if( yaexam_is_result() ) {

			$js_params['id']  = $post->ID;
		}
		
		wp_register_script( 'vue', $js_path . '/vue.js' );
		
		
		wp_enqueue_script( 'yaexam-frontend', $js_path . '/front.js', array('jquery', 'jquery-ui-sortable', 'underscore', 'vue'), '1.0.0');

		wp_localize_script( 'yaexam-frontend', 'yaexam', $js_params );
	}
}
YAEXAM_Frontend_Scripts::init();
