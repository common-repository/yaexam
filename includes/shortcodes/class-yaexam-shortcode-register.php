<?php

namespace YaExam\Shortcodes;

use YaExam\YAEXAM_Shortcodes;

defined( 'ABSPATH' ) || exit;

class YAEXAM_Shortcode_Register {
	
	public static function get( $atts ) {
		return YAEXAM_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}
	
	public static function output( $atts ) {
		global $wp;
		
		if ( ! is_user_logged_in() && (get_option( 'users_can_register' ) == 1) ) {
			
			self::register();
			
		}else{

			wp_redirect( home_url() );
		}
	}
	
	private static function register() {
				
		yaexam_get_template( 'myaccount/form-register.php' );
	}
}