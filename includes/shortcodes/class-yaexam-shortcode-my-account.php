<?php

namespace YaExam\Shortcodes;

defined( 'ABSPATH' ) || exit;
class YAEXAM_Shortcode_My_Account {
	
	public static function get( $atts ) {
		return YAEXAM_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}
	
	public static function output( $atts ) {
		global $wp;

		if ( is_user_logged_in() ) {
			
			if ( isset( $wp->query_vars['view-edit-account'] ) ) {

				self::edit_account();
				
			} elseif ( isset( $wp->query_vars['view-result'] ) ) {
				
				self::view_result();
			
			} elseif ( isset( $wp->query_vars['view-results'] ) ) {
				
				self::view_results();

			} elseif ( isset( $wp->query_vars['view-save-laters'] ) ) {
				
				self::view_save_laters();

			} else {

				$is_redirect = apply_filters( 'yaexam_my_account_tabs_content', true, $wp->query_vars );

				if( $is_redirect ){
					
					wp_redirect(yaexam_get_endpoint_url( 'view-results', '', yaexam_get_page_permalink( 'myaccount' ) ));
				}
			}
			
		}else{
			
			if ( isset( $wp->query_vars['em-lost-password'] ) ) {
				
				self::lost_password();
				
			} elseif ( isset( $wp->query_vars['reset-password'] ) ) {
				
				self::reset_password();

			} elseif ( isset( $wp->query_vars['view-activation'] ) ) {

				self::view_activation();
				
			} else {
				
				wp_redirect(yaexam_get_page_permalink('login'));
				exit;
			}
			
		}
		
	}
	
	private static function my_account( $atts ) {
		extract( shortcode_atts( array(
	    	'exam_count' => 15
		), $atts ) );

		yaexam_get_template( 'myaccount/my-account.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
			'exam_count' 	=> 'all' == $exam_count ? -1 : $exam_count,
			'logout_url'	=>	yaexam_get_endpoint_url( 'member-logout', '', yaexam_get_page_permalink( 'myaccount' ) ),
			'edit_url'		=>	yaexam_get_endpoint_url( 'view-edit-account', '', yaexam_get_page_permalink( 'myaccount' ) )
		) );
	}
	
	private static function edit_account() {
		
		yaexam_get_template( 'myaccount/form-edit-account.php', array( 'user' => get_user_by( 'id', get_current_user_id() ) ) );
	}
	
	private static function view_result() {
		global $wp;
		
		$exam_id	=	absInt($wp->query_vars['view-result']);
		$params		=	array();
		
		if($exam_id){
			
			$exam		=	YAEXAM()->exam_factory->get_exam($exam_id);

			$page		=	isset($wp->query_vars['em-p']) ? absInt($wp->query_vars['em-p']) : 1;
			
			$results	=	$exam->get_user_results(get_current_user_id(), $page);
			
			$params['exam']	=	$exam;
				
			if($results){
				
				$results['pagination']['link']	=	yaexam_get_endpoint_url( 'view-result', $exam_id, yaexam_get_page_permalink( 'myaccount' ) );
				
				$params['results']	=	$results;
			}
		}

		yaexam_get_template( 'myaccount/view-result.php', $params );
	}
	
	private static function view_results() {
		global $wp;
		
		$page		=	isset($wp->query_vars['em-p']) ? absInt($wp->query_vars['em-p']) : 1;
		
		$userid     =	apply_filters('yaexam_view_results_of_user', get_current_user_id());
		
		$results	=	yaexam_get_user_results( $userid, $page );
		
		$results['pagination']['link']	=	yaexam_get_endpoint_url( 'view-results', '', yaexam_get_page_permalink( 'myaccount' ) );
		
		yaexam_get_template( 'myaccount/view-results.php', $results);
	}

	private static function view_save_laters() {

		if( isset($_GET['remove']) ) {
			
			yaexam_remove_save_later(absint($_GET['remove']));

			yaexam_add_message( __( 'Remove success', 'yaexam' ), 'success' );
		}


		$items = yaexam_get_save_laters([
			'user_id' => get_current_user_id()
		]);

		yaexam_get_template( 'myaccount/view-save-laters.php', ['items' => $items]);
	}
	
	private static function login() {
		
		$redirect	=	yaexam_get_endpoint_url( 'view-results', '', yaexam_get_page_permalink( 'myaccount' ) );
		
		yaexam_get_template( 'myaccount/form-login.php', array('redirect' => $redirect) );
	}
	
	private static function lost_password() {
		
		$args = array( 'form' => 'lost_password' );

		if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {

			$user = self::check_password_reset_key( $_GET['key'], $_GET['login'] );

			if( is_object( $user ) ) {
				$args['form'] = 'reset_password';
				$args['key'] = esc_attr( $_GET['key'] );
				$args['login'] = esc_attr( $_GET['login'] );
			}
		} elseif ( isset( $_GET['reset'] ) ) {
			yaexam_add_message( __( 'Your password has been reset.', 'yaexam' ) . ' <a href="' . yaexam_get_page_permalink( 'myaccount' ) . '">' . __( 'Log in', 'yaexam' ) . '</a>' );
		}
		
		yaexam_get_template( 'myaccount/form-lost-password.php', $args );
	}
	
	public static function retrieve_password() {
		global $wpdb, $wp_hasher;

		$login = trim( $_POST['user_login'] );

		if ( empty( $login ) ) {

			yaexam_add_message( __( 'Enter a username or e-mail address.', 'yaexam' ), 'error' );
			return false;

		} else {
			// Check on username first, as customers can use emails as usernames.
			$user_data = get_user_by( 'login', $login );
		}

		// If no user found, check if it login is email and lookup user based on email.
		if ( ! $user_data && is_email( $login ) && apply_filters( 'yaexam_get_username_from_email', true ) ) {
			$user_data = get_user_by( 'email', $login );
		}

		do_action( 'lostpassword_post' );

		if ( ! $user_data ) {
			yaexam_add_message( __( 'Invalid username or e-mail.', 'yaexam' ), 'error' );
			return false;
		}

		if ( is_multisite() && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
			yaexam_add_message( __( 'Invalid username or e-mail.', 'yaexam' ), 'error' );
			return false;
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;

		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( ! $allow ) {

			yaexam_add_message( __( 'Password reset is not allowed for this user', 'yaexam' ), 'error' );
			return false;

		} elseif ( is_wp_error( $allow ) ) {

			yaexam_add_message( $allow->get_error_message(), 'error' );
			return false;
		}

		$key = wp_generate_password( 20, false );

		do_action( 'retrieve_password_key', $user_login, $key );

		$hashed = wp_hash_password( $key );

		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );
		
		do_action( 'yaexam_email_reset_password', $user_login, $key );

		yaexam_add_message( __( 'Check your e-mail for the confirmation link.', 'yaexam' ) );
		return true;
	}
	
	public static function check_password_reset_key( $key, $login ) {
		global $wpdb, $wp_hasher;

		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		if ( empty( $key ) || ! is_string( $key ) ) {
			yaexam_add_message( __( 'Invalid key', 'yaexam' ), 'error' );
			return false;
		}

		if ( empty( $login ) || ! is_string( $login ) ) {
			yaexam_add_message( __( 'Invalid key', 'yaexam' ), 'error' );
			return false;
		}

		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_login = %s", $login ) );
		
		if ( ! empty( $user ) ) {
			
			$valid = wp_check_password( $key, $user->user_activation_key, $user->ID );
		}

		if ( empty( $user ) || empty( $valid ) ) {
			yaexam_add_message( __( 'Invalid key', 'yaexam' ), 'error' );
			return false;
		}

		return get_userdata( $user->ID );
	}
	
	public static function reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );

		wp_set_password( $new_pass, $user->ID );

		wp_password_change_notification( $user );
	}
	

	public static function view_activation() {

		$status = -1;

		if( isset($_GET['em-activation']) && $_GET['em-activation'] ) {

			if( $_GET['em-activation'] == 'new' ) {

				$status = 0;

			}elseif( $_GET['em-activation'] == 'required' ){

				$status = 1;

			}else{

				$user = yaexam_user_validate_activation_key($_GET['em-activation']);

				if( $user ) {

					$status = 2;

				}else{

					$status = 3;

				}

			}
		}

		if( $status == -1 ) {

			wp_redirect( site_url('/') ); exit;

		}else{

			yaexam_get_template( 'myaccount/view-activation.php', ['status' => $status] );
		}
	}
}