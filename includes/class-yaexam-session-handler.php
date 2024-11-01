<?php

namespace YaExam;

defined( 'ABSPATH' ) || exit;

use YaExam\Abstracts\YAEXAM_Session;

class YAEXAM_Session_Handler extends YAEXAM_Session {

	/** @var string cookie name */
	private $_cookie;

	/** @var string session due to expire timestamp */
	private $_session_expiring;

	/** @var string session expiration timestamp */
	private $_session_expiration;

	/** $var bool Bool based on whether a cookie exists **/
	private $_has_cookie = false;

	/** @var string Custom session table name */
	private $_table;

	/**
	 * Constructor for the session class.
	 */
	public function __construct( $params = array() ) {
		global $wpdb;

		$this->_cookie = 'wp_yaexam_session_' . COOKIEHASH;
		$this->_table  = $wpdb->prefix . 'yaexam_sessions';

		if ( $cookie = $this->get_session_cookie() ) {
			$this->_member_id			= $cookie[0];
			$this->_session_expiration	= $cookie[1];
			$this->_session_expiring	= $cookie[2];
			$this->_has_cookie        	= true;

			// Update session if its close to expiring
			if ( time() > $this->_session_expiring ) {
				$this->set_session_expiration();
				$this->update_session_timestamp( $this->_member_id, $this->_session_expiration );
			}

		} else {
			$this->set_session_expiration();
			$this->_member_id = $this->generate_member_id();
		}

		$this->_data = $this->get_session_data();

		// Actions
		add_action( 'yaexam_set_doing_cookies', array( $this, 'set_member_session_cookie' ), 10 );
		// add_action( 'yaexam_cleanup_sessions', array( $this, 'cleanup_sessions' ), 10 );
		add_action( 'shutdown', array( $this, 'save_data' ), 20 );
		add_action( 'wp_logout', array( $this, 'destroy_session' ) );
		if ( ! is_user_logged_in() ) {
			add_filter( 'nonce_user_logged_out', array( $this, 'nonce_user_logged_out' ) );
		}
	}

	public function set_member_session_cookie( $set ) {
		if ( $set ) {
			// Set/renew our cookie
			$to_hash           = $this->_member_id . '|' . $this->_session_expiration;
			$cookie_hash       = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
			$cookie_value      = $this->_member_id . '||' . $this->_session_expiration . '||' . $this->_session_expiring . '||' . $cookie_hash;
			$this->_has_cookie = true;

			// Set the cookie
			yaexam_setcookie( $this->_cookie, $cookie_value, $this->_session_expiration, apply_filters( 'yaexam_session_use_secure_cookie', false ) );
		}
	}

	public function has_session() {
		return isset( $_COOKIE[ $this->_cookie ] ) || $this->_has_cookie || is_user_logged_in();
	}

	/**
	 * Set session expiration.
	 */
	public function set_session_expiration() {
		$this->_session_expiring   = yaexam_time() + intval( apply_filters( 'yaexam_session_expiring', 60 * 60 * 47 ) ); // 47 Hours.
		$this->_session_expiration = yaexam_time() + intval( apply_filters( 'yaexam_session_expiration', 60 * 60 * 48 ) ); // 48 Hours.
	}

	public function generate_member_id() {
		if ( is_user_logged_in() ) {
			return get_current_user_id();
		} else {
			$hashpasword = wp_hash_password(wp_generate_password( 8, true ));
			return md5( $hashpasword );
		}
	}

	public function get_session_cookie() {
		if ( empty( $_COOKIE[ $this->_cookie ] ) ) {
			return false;
		}

		list( $member_id, $session_expiration, $session_expiring, $cookie_hash ) = explode( '||', $_COOKIE[ $this->_cookie ] );

		// Validate hash
		$to_hash = $member_id . '|' . $session_expiration;
		$hash    = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );

		if ( empty( $cookie_hash ) || ! hash_equals( $hash, $cookie_hash ) ) {
			return false;
		}

		return array( $member_id, $session_expiration, $session_expiring, $cookie_hash );
	}

	public function get_session_data() {
		return $this->has_session() ? (array) $this->get_session( $this->_member_id, array() ) : array();
	}

	private function get_cache_prefix() {
			
		return 'yaexam_session_id';
	}

	/**
	 * Save data.
	 */
	public function save_data() {
		// Dirty if something changed - prevents saving nothing new
		if ( $this->_dirty && $this->has_session() ) {
			global $wpdb;

			$wpdb->replace(
				$this->_table,
				array(
					'session_key' => $this->_member_id,
					'session_value' => maybe_serialize( $this->_data ),
					'session_expiry' => $this->_session_expiration
				),
				array(
					'%s',
					'%s',
					'%d'
				)
			);

			// Set cache
			wp_cache_set( $this->get_cache_prefix() . $this->_member_id, $this->_data, YAEXAM_SESSION_CACHE_GROUP, $this->_session_expiration - yaexam_time() );

			// Mark session clean after saving
			$this->_dirty = false;
		}
	}

	/**
	 * Destroy all session data.
	 */
	public function destroy_session() {
		// Clear cookie
		yaexam_setcookie( $this->_cookie, '', yaexam_time() - YEAR_IN_SECONDS, apply_filters( 'yaexam_session_use_secure_cookie', false ) );

		$this->delete_session( $this->_member_id );

		// Clear data
		$this->_data        = array();
		$this->_dirty       = false;
		$this->_member_id 	= $this->generate_member_id();
	}

	/**
	 * When a user is logged out, ensure they have a unique nonce by using the customer/session ID.
	 *
	 * @return string
	 */
	public function nonce_user_logged_out( $uid ) {
		return $this->has_session() && $this->_member_id ? $this->_member_id : $uid;
	}

	/**
	 * Cleanup sessions.
	 */
	public function cleanup_sessions() {
		global $wpdb;

		if ( ! defined( 'WP_SETUP_CONFIG' ) && ! defined( 'WP_INSTALLING' ) ) {

			// Delete expired sessions
			$wpdb->query( $wpdb->prepare( "DELETE FROM $this->_table WHERE session_expiry < %d", yaexam_time() ) );

		}
	}

	public function get_session( $member_id, $default = false ) {
		global $wpdb;

		if ( defined( 'WP_SETUP_CONFIG' ) ) {
			return false;
		}

		// Try get it from the cache, it will return false if not present or if object cache not in use
		$value = wp_cache_get( $this->get_cache_prefix() . $member_id, YAEXAM_SESSION_CACHE_GROUP );

		if ( false === $value ) {
			$value = $wpdb->get_var( $wpdb->prepare( "SELECT session_value FROM $this->_table WHERE session_key = %s", $member_id ) );

			if ( is_null( $value ) ) {
				$value = $default;
			}

			wp_cache_add( $this->get_cache_prefix() . $member_id, $value, YAEXAM_SESSION_CACHE_GROUP, $this->_session_expiration - time() );
		}

		return maybe_unserialize( $value );
	}
	
	public function delete_session( $member_id ) {
		global $wpdb;

		wp_cache_delete( $this->get_cache_prefix() . $member_id, YAEXAM_SESSION_CACHE_GROUP );

		$wpdb->delete(
			$this->_table,
			array(
				'session_key' => $member_id
			)
		);
	}

	public function update_session_timestamp( $member_id, $timestamp ) {
		global $wpdb;

		$wpdb->update(
			$this->_table,
			array(
				'session_expiry' => $timestamp
			),
			array(
				'session_key' => $member_id
			),
			array(
				'%d'
			)
		);
	}

	public function update_instant_answer( $question_id, $answer = false ){

		$session_data	=	$this->get('doing');

		$session_data['instant_answers'][$question_id] = $answer;

		$this->set('doing', $session_data);
	}
}
