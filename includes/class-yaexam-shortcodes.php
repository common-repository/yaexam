<?php

namespace YaExam;

use YaExam\Shortcodes\YAEXAM_Shortcode_My_Account;
use YaExam\Shortcodes\YAEXAM_Shortcode_Login;
use YaExam\Shortcodes\YAEXAM_Shortcode_Register;

defined( 'ABSPATH' ) || exit;
class YAEXAM_Shortcodes {
	
	public static function init() {

		if( is_admin() ) { return; }

		$shortcodes = array(
			'yaexam_my_account'		=>	__CLASS__ . '::my_account',
			'yaexam_register'		=>	__CLASS__ . '::register',
			'yaexam_login'			=>	__CLASS__ . '::login',
		);
		
		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
		
	}
	
	public static function shortcode_wrapper(
		
		$function,
		$atts    = array(),
		$wrapper = array(
			'class'  => 'yaexam',
			'before' => null,
			'after'  => null
		)
	) {
		ob_start();
		
		echo empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		call_user_func( $function, $atts );
		echo empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];
		
		return ob_get_clean();
	}
	
	
	public static function my_account() {
		return self::shortcode_wrapper( array( YAEXAM_Shortcode_My_Account::class, 'output' ) );
	}

	public static function register() {
		return self::shortcode_wrapper( array( YAEXAM_Shortcode_Register::class, 'output' ) );
	}

	public static function login() {
		return self::shortcode_wrapper( array( YAEXAM_Shortcode_Login::class, 'output' ) );
	}
}