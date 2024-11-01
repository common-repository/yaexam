<?php

namespace YaExam;

use YaExam\YAEXAM_Doing;

defined( 'ABSPATH' ) || exit;

class YAEXAM_Form_Handler {
	
	public static function init() {
		
		add_action( 'template_redirect', array( __CLASS__, 'save_account_details' ) );
		add_action( 'template_redirect', array( __CLASS__, 'save_register' ) );
		add_action( 'template_redirect', array( __CLASS__, 'check_valid_doing' ) );
		
		add_action( 'wp_loaded', array( __CLASS__, 'start_exam' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'submit_exam' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_login' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_lost_password' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_reset_password' ), 20 );

	}

	public static function check_valid_doing() {

		if( isset($_GET['em-doing']) ) {

			if( !yaexam_is_valid_session( yaexam_clean($_GET['em-doing'])) ) {

				wp_redirect( home_url() );
				exit;
			}
		}
	}
	
	public static function start_exam(){
		
		if(isset($_POST['yaexam_start_exam']) || isset($_POST['yaexam_start_exam_later']))
		{

			
			if(isset($_POST['yaexam_start_exam']) && absint($_POST['yaexam_start_exam']) == 1)
			{
				yaexam_remove_save_later_by_exam_id( get_current_user_id(), absInt($_POST['id']) );
				yaexam_remove_user_session_when_doing(get_current_user_id(), absInt($_POST['id']));
			}
			
			YAEXAM()->doing()->start_exam();
		}
	}
	
	public static function submit_exam(){
		
		if(isset($_POST['yaexam_submit_exam']))
		{
			YAEXAM()->doing()->submit_exam();
		}
	}
	
	public static function save_account_details() {

		global $wpdb; 
		
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			return;
		}
		
		if ( empty( $_POST[ 'action' ] ) || 'yaexam_save_account_details' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'yaexam_save_account_details' ) ) {
			return;
		}
		
		$errors       = YAEXAM()->error_handler();
		$user         = yaexam_init_class();

		$user->ID     = (int) get_current_user_id();
		$current_user = get_user_by( 'id', $user->ID );
		
		if ( $user->ID <= 0 ) {
			return;
		}
		
		$account_first_name = ! empty( $_POST[ 'account_first_name' ] ) ? yaexam_clean( wp_unslash($_POST[ 'account_first_name' ]) ) : '';
		$account_last_name  = ! empty( $_POST[ 'account_last_name' ] ) ? yaexam_clean( wp_unslash($_POST[ 'account_last_name' ]) ) : '';
		$account_email      = ! empty( $_POST[ 'account_email' ] ) ? sanitize_email( yaexam_clean(wp_unslash($_POST[ 'account_email' ])) ) : '';

		$username 			= ! empty( $_POST[ 'account_username' ] ) ? sanitize_user( yaexam_clean(wp_unslash($_POST[ 'account_username' ])), 50 ) : $current_user->user_login;
		$pass1              = ! empty( $_POST[ 'password_1' ] ) ? $_POST[ 'password_1' ] : '';
		$pass2              = ! empty( $_POST[ 'password_2' ] ) ? $_POST[ 'password_2' ] : '';
		$save_pass          = true;
		
		$user->first_name   = $account_first_name;
		$user->last_name    = $account_last_name;

		// Prevent emails being displayed, or leave alone.
		$user->display_name = is_email( $current_user->display_name ) ? $user->first_name : $current_user->display_name;
		
		// Handle required fields
		$required_fields = apply_filters( 'yaexam_save_account_details_required_fields', array(
			'account_first_name' => __( 'First Name', 'yaexam' ),
			'account_last_name'  => __( 'Last Name', 'yaexam' ),
			'account_email'      => __( 'Email address', 'yaexam' ),
		) );
		
		foreach ( $required_fields as $field_key => $field_name ) {
			if ( empty( $_POST[ $field_key ] ) ) {
				yaexam_add_message( '<strong>' . esc_html( $field_name ) . '</strong> ' . __( 'is a required field.', 'yaexam' ), 'error' );
			}
		}
		
		if ( $account_email ) {
			if ( ! is_email( $account_email ) ) {
				yaexam_add_message( __( 'Please provide a valid email address.', 'yaexam' ), 'error' );
			} elseif ( email_exists( $account_email ) && $account_email !== $current_user->user_email ) {
				yaexam_add_message( __( 'This email address is already registered.', 'yaexam' ), 'error' );
			}
			$user->user_email = $account_email;
		}

		if( $username != $current_user->user_login ) {
			
			if( username_exists( $username ) ) {

				yaexam_add_message( __( 'This username is already registered.', 'yaexam' ), 'error' );

			}
					
		}

		if ( ! empty( $pass1 ) && empty( $pass2 ) ) {
			yaexam_add_message( __( 'Please re-enter your password.', 'yaexam' ), 'error' );
			$save_pass = false;
		} elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
			yaexam_add_message( __( 'New passwords do not match.', 'yaexam' ), 'error' );
			$save_pass = false;
		}
		
		if ( $pass1 && $save_pass ) {
			$user->user_pass = $pass1;
		}
		
		// Allow plugins to return their own errors.
		do_action_ref_array( 'yaexam_save_account_details_errors', array( &$errors, &$user ) );
		
		if ( $errors->get_error_messages() ) {
			foreach ( $errors->get_error_messages() as $error ) {
				yaexam_add_message( $error, 'error' );
			}
		}
		
		if ( yaexam_message_count( 'error' ) === 0 ) {
			
			wp_update_user( $user ) ;
			delete_user_meta( $user->ID, 'is_guest' );

			if( $username != $current_user->user_login ) {

				$wpdb->update( $wpdb->prefix . 'users', array(
							'user_login'	=>	$username,
							'user_nicename' =>	$username,
							'display_name'	=>	$username
							), array('ID' => $user->ID), array('%s', '%s', '%s') );

			}
			
			yaexam_add_message( __( 'Account details changed successfully.', 'yaexam' ) );

			do_action( 'yaexam_save_account_details', $user->ID );
			
			if(isset($_POST['redirect'])){
				
				wp_redirect( wp_sanitize_redirect(wp_unslash($_POST['redirect'])) );
				exit;
			}

		}
		
	}
	
	public static function save_register() {
		
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			return;
		}
		
		if ( empty( $_POST[ 'action' ] ) || 'yaexam_save_register' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'yaexam_save_register' ) ) {
			return;
		}
		
		$errors       = YAEXAM()->error_handler();

		$params_new_user	=	array();

		$params_new_user['is_email_for_username']	=	get_option('yaexam_is_email_for_username');

		$userData = [
			'username'	=>	(! empty( $_POST[ 'username' ] ) ? sanitize_user( $_POST[ 'username' ], 50 ) : ''),
			'email'      =>	(! empty( $_POST[ 'email' ] ) ? sanitize_email( $_POST[ 'email' ] ) : ''),
			'password'   =>	(! empty( $_POST[ 'password' ] ) ? $_POST[ 'password' ] : '')
		];
		
		$user	=	yaexam_get_new_user( $userData, $params_new_user );

		do_action_ref_array( 'yaexam_before_register', array( $userData, &$user, &$errors ) );
		
		do_action_ref_array( 'yaexam_save_register_errors', array( &$errors, &$user ) );
			
		if ( $errors->get_error_messages() ) {
			foreach ( $errors->get_error_messages() as $error ) {
				yaexam_add_message( $error, 'error' );
			}
		}
		
		if ( yaexam_message_count( 'error' ) === 0 ) {
			
			$user_id = wp_insert_user( $user ) ;

			$secure_cookie          = is_ssl() ? true : false;

			$user   = wp_signon( apply_filters( 'yaexam_login_credentials', [
				'user_login' 	=> $user->user_login,
				'user_password' => $user->user_pass,
				'remember' 		=> true
			] ), $secure_cookie );

			do_action( 'yaexam_after_register', $user_id );
			do_action( 'yaexam_email_new_member', $user_id );
			
			wp_safe_redirect( yaexam_get_page_permalink( 'myaccount' ) );
			exit;
		}
		
	}
	
	public static function process_login() {
		
		if ( ! empty( $_POST['login'] ) && ! empty( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'yaexam-login' ) ) {
			
			try {
				$creds    = array();
				$username = yaexam_clean( $_POST['username'] );

				$validation_error = YAEXAM()->error_handler();
				$validation_error = apply_filters( 'yaexam_process_login_errors', $validation_error, $username, $_POST['password'] );

				if ( $validation_error->get_error_code() ) {

					yaexam_add_message( $validation_error->get_error_message(), 'error' );

				}

				if ( empty( $username ) ) {

					yaexam_add_message( __( 'Username is required.', 'yaexam' ), 'error' );
				}

				if ( !validate_username( $username ) ) {

					yaexam_add_message( __( 'Username is not valid.', 'yaexam' ), 'error' );
				}

				if ( empty( $_POST['password'] ) ) {

					yaexam_add_message( __( 'Password is required.', 'yaexam' ), 'error' );
				}

				if ( is_email( $username ) && apply_filters( 'yaexam_get_username_from_email', true ) ) {
					$user = get_user_by( 'email', $username );

					if ( isset( $user->user_login ) ) {
						$creds['user_login'] = $user->user_login;
					} else {

						yaexam_add_message( __( 'A user could not be found with this email address.', 'yaexam' ), 'error' );
					}

				} else {
					$creds['user_login'] = sanitize_user($username, true);
				}

			
				if ( yaexam_message_count( 'error' ) === 0 ) {

					$creds['user_password'] = $_POST['password'];
					$creds['remember']      = isset( $_POST['rememberme'] ) ? true : false;
					$secure_cookie          = is_ssl() ? true : false;

					$user                   = wp_signon( apply_filters( 'yaexam_login_credentials', $creds ), $secure_cookie );
					
					if ( is_wp_error( $user ) ) {
						$message = $user->get_error_message();
						$message = str_replace( '<strong>' . esc_html( $creds['user_login'] ) . '</strong>', '<strong>' . esc_html( $username ) . '</strong>', $message );
						
						yaexam_add_message( $message, 'error' );

					} else {

						if ( ! empty( $_POST['redirect'] ) ) {
							$redirect = wp_sanitize_redirect( wp_unslash($_POST['redirect']) );
						} elseif ( wp_get_referer() ) {
							$redirect = wp_get_referer();
						} else {
							$redirect = yaexam_get_page_permalink( 'myaccount' );
						}

						// Feedback
						yaexam_add_message( sprintf( __( 'You are now logged in as <strong>%s</strong>', 'yaexam' ), $user->display_name ) );

						wp_redirect( apply_filters( 'yaexam_login_redirect', $redirect, $user ) );
						exit;
					}

				}

			} catch (Exception $e) {	

				yaexam_add_message( apply_filters('login_errors', $e->getMessage() ), 'error' );

			}
			
		}
	}
	
	public static function process_lost_password() {
		if ( ! empty( $_POST['user_login'] ) && ! empty( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'yaexam-reset-password' ) ) {
			
			YAEXAM_Shortcode_My_Account::retrieve_password();			
		}
	}
	
	public static function process_reset_password() {
		
		$posted_fields = array( 'password_1', 'password_2', 'reset_key', 'reset_login', '_wpnonce' );

		foreach ( $posted_fields as $field ) {
			if ( ! isset( $_POST[ $field ] ) ) {
				return;
			}
			
			$posted_fields[ $field ] = yeaxam_clean(wp_unslash($_POST[ $field ]));
		}

		if ( ! wp_verify_nonce( $posted_fields['_wpnonce'], 'yaexam-reset-password' ) ) {
			return;
		}

		$user = YAEXAM_Shortcode_My_Account::check_password_reset_key( $posted_fields['reset_key'], $posted_fields['reset_login'] );

		if ( yaexam_instanceof_wp_user($user) ) {
			if ( empty( $posted_fields['password_1'] ) ) {
				yaexam_add_message( __( 'Please enter your password.', 'yaexam' ), 'error' );
			}

			if ( $posted_fields[ 'password_1' ] !== $posted_fields[ 'password_2' ] ) {
				yaexam_add_message( __( 'Passwords do not match.', 'yaexam' ), 'error' );
			}

			$errors = YAEXAM()->error_handler();

			do_action( 'validate_password_reset', $errors, $user );

			if ( empty( $errors->get_error_codes() ) ) {
				YAEXAM_Shortcode_My_Account::reset_password( $user, $posted_fields['password_1'] );

				do_action( 'yaexam_reset_password', $user );

				wp_redirect( add_query_arg( 'reset', 'true', remove_query_arg( array( 'key', 'login' ) ) ) );
				exit;
			}
		}
		
	}

}

YAEXAM_Form_Handler::init();
