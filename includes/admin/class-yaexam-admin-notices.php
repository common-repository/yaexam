<?php

namespace YaExam\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * YAEXAM_Admin_Notices Class.
 */
class YAEXAM_Admin_Notices {

	private static $notices = array();

	private static $core_notices = array(
		'install'             => 'install_notice',
		'update'              => 'update_notice'
	);

	public static function init() {
		self::$notices = get_option( 'yaexam_admin_notices', array() );

		add_action( 'switch_theme', array( __CLASS__, 'reset_admin_notices' ) );
		add_action( 'yaexam_installed', array( __CLASS__, 'reset_admin_notices' ) );
		add_action( 'wp_loaded', array( __CLASS__, 'hide_notices' ) );
		add_action( 'shutdown', array( __CLASS__, 'store_notices' ) );

		if ( current_user_can( 'manage_yaexam' ) ) {
			add_action( 'admin_print_styles', array( __CLASS__, 'add_notices' ) );
		}
	}

	/**
	 * Store notices to DB
	 */
	public static function store_notices() {
		update_option( 'yaexam_admin_notices', self::get_notices() );
	}

	/**
	 * Get notices
	 * @return array
	 */
	public static function get_notices() {
		return self::$notices;
	}

	/**
	 * Remove all notices.
	 */
	public static function remove_all_notices() {
		self::$notices = array();
	}

	/**
	 * Reset notices for themes when switched or a new version of WC is installed.
	 */
	public static function reset_admin_notices() {

		
	}

	/**
	 * Show a notice.
	 * @param string $name
	 */
	public static function add_notice( $name ) {
		self::$notices = array_unique( array_merge( self::get_notices(), array( $name ) ) );
	}

	/**
	 * Remove a notice from being displayed.
	 * @param  string $name
	 */
	public static function remove_notice( $name ) {
		self::$notices = array_diff( self::get_notices(), array( $name ) );
		delete_option( 'yaexam_admin_notice_' . $name );
	}

	/**
	 * See if a notice is being shown.
	 * @param  string  $name
	 * @return boolean
	 */
	public static function has_notice( $name ) {
		return in_array( $name, self::get_notices() );
	}

	/**
	 * Hide a notice if the GET variable is set.
	 */
	public static function hide_notices() {
		if ( isset( $_GET['em-hide-notice'] ) && isset( $_GET['_yaexam_notice_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_yaexam_notice_nonce'], 'yaexam_hide_notices_nonce' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'yaexam' ) );
			}

			if ( ! current_user_can( 'manage_yaexam' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'yaexam' ) );
			}

			$hide_notice = sanitize_text_field( $_GET['em-hide-notice'] );
			self::remove_notice( $hide_notice );
			do_action( 'yaexam_hide_' . $hide_notice . '_notice' );
		}
	}

	/**
	 * Add notices + styles if needed.
	 */
	public static function add_notices() {
		$notices = self::get_notices();

		if ( ! empty( $notices ) ) {
			//wp_enqueue_style( 'yaexam-activation', plugins_url(  '/assets/css/activation.css', YAEXAM_PLUGIN_FILE ) );
			foreach ( $notices as $notice ) {
				if ( ! empty( self::$core_notices[ $notice ] ) && apply_filters( 'yaexam_show_admin_notice', true, $notice ) ) {
					add_action( 'admin_notices', array( __CLASS__, self::$core_notices[ $notice ] ) );
				} else {
					add_action( 'admin_notices', array( __CLASS__, 'output_custom_notices' ) );
				}
			}
		}
	}

	/**
	 * Add a custom notice.
	 * @param string $name
	 * @param string $notice_html
	 */
	public static function add_custom_notice( $name, $notice_html ) {
		self::add_notice( $name );
		update_option( 'yaexam_admin_notice_' . $name, wp_kses_post( $notice_html ) );
	}

	/**
	 * Output any stored custom notices.
	 */
	public static function output_custom_notices() {
		$notices = self::get_notices();

		if ( ! empty( $notices ) ) {
			foreach ( $notices as $notice ) {
				if ( empty( self::$core_notices[ $notice ] ) ) {
					$notice_html = get_option( 'yaexam_admin_notice_' . $notice );

					if ( $notice_html ) {
						include( 'views/html-notice-custom.php' );
					}
				}
			}
		}
	}

	/**
	 * If we need to update, include a message with the update button.
	 */
	public static function update_notice() {
		if ( empty( $_GET['do_update_yaexam'] ) && version_compare( get_option( 'yaexam_db_version' ), YAEXAM_VERSION, '<' ) ) {
						
			include( 'views/html-notice-update.php' );
			
		} else {
			include( 'views/html-notice-updated.php' );
		}
	}

	/**
	 * If we have just installed, show a message with the install pages button.
	 */
	public static function install_notice() {
		include( 'views/html-notice-install.php' );
	}

}

YAEXAM_Admin_Notices::init();