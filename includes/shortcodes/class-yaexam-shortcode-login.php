<?php

namespace YaExam\Shortcodes;

defined( 'ABSPATH' ) || exit;

class YAEXAM_Shortcode_Login {
	
	public static function get( $atts ) {
		return YAEXAM_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}
	
	public static function output( $atts ) {
		global $wp;
		
		if ( ! is_user_logged_in() ) {
			
			self::login();
			
		} 
	}
	
	private static function login() {
		
		yaexam_login_form(
			array(
				'message'  => __( 'If you have shopped with us before, please enter your details in the boxes below. If you are a new customer, please proceed to the Billing &amp; Shipping section.', 'yaexam' ),
				'redirect' => yaexam_get_page_permalink( 'myaccount' ),
				'hidden'   => true
			)
		);
	}
}