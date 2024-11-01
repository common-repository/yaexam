<?php
namespace YaExam;

use YaExam\YAEXAM_Background_Updater;
use YaExam\YAEXAM_Post_Type_Exam;
use YaExam\Admin\YAEXAM_Admin_Notices;
use YaExam\Admin\YAEXAM_Admin_Settings;

defined( 'ABSPATH' ) || exit;

/**
 * YAEXAM_Install Class.
 */
class YAEXAM_Install {

	private static $db_updates = array();

	private static $background_updater;
	
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
	}

	/**
	 * Init background updates
	 */
	public static function init_background_updater() {
		
		include_once( dirname( __FILE__ ) . '/class-yaexam-background-updater.php' );
		self::$background_updater = new YAEXAM_Background_Updater();
		
	}
	
	public static function check_version() {
	

		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'yaexam_version' ) !== YAEXAM()->version ) {
			
			self::install();
			do_action( 'yaexam_updated' );
		}
	}

	public static function install_actions() {
		
		if ( ! empty( $_GET['do_update_yaexam'] ) ) {
			self::update();
			YAEXAM_Admin_Notices::add_notice( 'update' );
		}
		
	}
	
	/**
	 * Install EM.
	 */
	public static function install() {
		global $wpdb;
		
		if ( ! defined( 'YAEXAM_INSTALLING' ) ) {
			define( 'YAEXAM_INSTALLING', true );
		}

		// Ensure needed classes are loaded
		// include_once( 'admin/class-yaexam-admin-notices.php' );
		
		self::create_options();
		self::create_tables();
		self::create_roles();
		self::create_pages();
		
		$current_yaexam_version = get_option( 'yaexam_version', null );
		$current_db_version     = get_option( 'yaexam_db_version', null );
		
		YAEXAM_Admin_Notices::remove_all_notices();

		YAEXAM_Post_Type_Exam::register_post_types();
		YAEXAM_Post_Type_Exam::register_taxonomies();

		if ( !empty(self::$db_updates) && ! is_null( $current_db_version ) && version_compare( $current_db_version, max( array_keys( self::$db_updates ) ), '<' ) ) {

			YAEXAM_Admin_Notices::add_notice( 'update' );

		} else {

			self::update_db_version();
		}

		// self::update_db_version();
		self::update_yaexam_version();
		
		flush_rewrite_rules();
		
		$sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
			AND b.option_value < %d";
		$wpdb->query( $wpdb->prepare( $sql, $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_transient_timeout_' ) . '%', time() ) );
		
		// Trigger action
		do_action( 'yaexam_installed' );

		
	}
	
	private static function update_yaexam_version() {
		delete_option( 'yaexam_version' );
		add_option( 'yaexam_version', YAEXAM()->version );
	}
	
	public static function update_db_version( $version = null ) {
		delete_option( 'yaexam_db_version' );
		add_option( 'yaexam_db_version', is_null( $version ) ? YAEXAM()->version : $version );
	}

	public static function cron_schedules( $schedules ) {
		$schedules['monthly'] = array(
			'interval' => 2635200,
			'display'  => __( 'Monthly', 'yaexam' )
		);
		return $schedules;
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 */
	private static function update() {
		
		$current_db_version = get_option( 'yaexam_db_version' );
		//$logger             = new YAEXAM_Logger();
		$update_queued      = false;

		include_once( dirname( __FILE__ ) . '/class-yaexam-background-updater.php' );
		self::$background_updater = new YAEXAM_Background_Updater();
		
		foreach ( self::$db_updates as $version => $update_callbacks ) {

			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					//$logger->add( 'em_db_updates', sprintf( 'Queuing %s - %s', $version, $update_callback ) );

					self::$background_updater->push_to_queue( $update_callback );
					$update_queued = true;
				}
			}
		}

		if ( $update_queued ) {

			self::$background_updater->save()->dispatch();
		}
	}
	
	/**
	 * Create roles and capabilities.
	 */
	public static function create_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		// Manager role
		add_role( 'yaexam_manager', __( 'YaExam Manager', 'yaexam' ), array(
			'level_9'                => true,
			'level_8'                => true,
			'level_7'                => true,
			'level_6'                => true,
			'level_5'                => true,
			'level_4'                => true,
			'level_3'                => true,
			'level_2'                => true,
			'level_1'                => true,
			'level_0'                => true,
			'read'                   => true,
			'read_private_pages'     => true,
			'read_private_posts'     => true,
			'edit_users'             => true,
			'edit_posts'             => true,
			'edit_pages'             => true,
			'edit_published_posts'   => true,
			'edit_published_pages'   => true,
			'edit_private_pages'     => true,
			'edit_private_posts'     => true,
			'edit_others_posts'      => true,
			'edit_others_pages'      => true,
			'publish_posts'          => true,
			'publish_pages'          => true,
			'delete_posts'           => true,
			'delete_pages'           => true,
			'delete_private_pages'   => true,
			'delete_private_posts'   => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'delete_others_posts'    => true,
			'delete_others_pages'    => true,
			'manage_categories'      => true,
			'manage_links'           => true,
			'moderate_comments'      => true,
			'unfiltered_html'        => true,
			'upload_files'           => true,
			'export'                 => true,
			'import'                 => true,
			'list_users'             => true
		) );

		$capabilities = self::get_core_capabilities();

		foreach ( $capabilities as $cap_group ) {
			foreach ( $cap_group as $cap ) {
				$wp_roles->add_cap( 'yaexam_manager', $cap );
				$wp_roles->add_cap( 'administrator', $cap );
			}
		}
	}

	private static function get_core_capabilities() {
		$capabilities = array();

		$capabilities['core'] = array(
			'manage_yaexam'
		);
		
		$capability_types = array( 'exam', 'emquestion', 'emcertificate' );
		
		foreach ( $capability_types as $capability_type ) {
			
			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms"
			);
		}

		return $capabilities;
	}

	public static function remove_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$capabilities = self::get_core_capabilities();

		foreach ( $capabilities as $cap_group ) {
			foreach ( $cap_group as $cap ) {
				$wp_roles->remove_cap( 'yaexam_manager', $cap );
				$wp_roles->remove_cap( 'administrator', $cap );
			}
		}

		remove_role( 'yaexam_manager' );
	}
	
	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( self::get_schema() );
	}
	
	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}
		
		$tables = "
CREATE TABLE {$wpdb->prefix}yaexam_sessions (
  session_id bigint(20) NOT NULL AUTO_INCREMENT,
  session_orders mediumtext NOT NULL,
  session_key char(32) NOT NULL,
  session_value longtext NOT NULL,
  session_expiry bigint(20) NOT NULL,
  UNIQUE KEY session_id (session_id),
  PRIMARY KEY  (session_key)
) $collate;
CREATE TABLE {$wpdb->prefix}yaexam_questions (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  category_id bigint(20) NOT NULL,
  title mediumtext COLLATE utf8_unicode_ci NOT NULL,
  content text COLLATE utf8_unicode_ci NOT NULL,
  explanation mediumtext COLLATE utf8_unicode_ci NOT NULL,
  score int(10) NOT NULL DEFAULT 0,
  video text COLLATE utf8_unicode_ci NULL,
  answer_type varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  order_type int(10) NOT NULL DEFAULT 0,
  timeout int(10) NOT NULL DEFAULT 0,
  answers longtext COLLATE utf8_unicode_ci NOT NULL,
  params longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}yaexam_question_categories (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  name mediumtext COLLATE utf8_unicode_ci NOT NULL,
  content text COLLATE utf8_unicode_ci NOT NULL,
  total int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}yaexam_results (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint(20) NOT NULL,
  exam_id bigint(20) NOT NULL,
  ranking_code varchar(100) COLLATE utf8_unicode_ci NULL,
  ranking_id bigint(20) unsigned NULL,
  percent smallint(6) NOT NULL DEFAULT 0,
  score int(10) NOT NULL DEFAULT 0,
  total_score int(10) NOT NULL DEFAULT 0,
  total_corrects int(10) NOT NULL DEFAULT 0,
  total_wrongs int(10) NOT NULL DEFAULT 0,
  total_notanswereds int(10) NOT NULL DEFAULT 0,
  exam_duration int(10) NOT NULL DEFAULT 0,
  date_start datetime NOT NULL,
  date_end datetime NOT NULL,
  duration time NOT NULL,
  others longtext NOT NULL,
  PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}yaexam_save_later (
	id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	user_id bigint(20) unsigned NOT NULL,
	exam_id bigint(20) unsigned NOT NULL,
	session_id varchar(255) COLLATE utf8_general_ci NOT NULL,
	PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}yaexam_exam_questions (
	id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	title text COLLATE utf8_unicode_ci NULL,
	exam_id bigint(20) unsigned NOT NULL,
	question_type varchar(100) COLLATE utf8_unicode_ci NOT NULL,
	question_id bigint(6) unsigned NOT NULL DEFAULT 0,
	question_params longtext COLLATE utf8_unicode_ci NULL,
	question_order bigint(20) unsigned NOT NULL DEFAULT 1,
	PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}yaexam_exam_results (
	id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	user_id bigint(20) unsigned NOT NULL,
	exam_id bigint(20) unsigned NOT NULL,
	user_attempts int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}yaexam_user_questions (
	id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	user_id bigint(20) unsigned NOT NULL,
	exam_id bigint(20) unsigned NOT NULL,
	question_id bigint(20) unsigned NOT NULL,
	question_data longtext COLLATE utf8_unicode_ci NOT NULL,
	question_answered longtext COLLATE utf8_unicode_ci NULL,
	question_result longtext COLLATE utf8_unicode_ci NULL,
	question_right_score int(10) unsigned NOT NULL DEFAULT 1,
	question_wrong_score int(10) unsigned NOT NULL DEFAULT 0,
	question_order bigint(20) unsigned NOT NULL DEFAULT 1,
	session_id varchar(255) COLLATE utf8_general_ci NOT NULL,
	result_id bigint(20) unsigned NULL,
	PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}yaexam_user_sessions (
	id varchar(255) COLLATE utf8_general_ci NOT NULL,
	user_id bigint(20) unsigned NOT NULL,
	exam_id bigint(20) unsigned NOT NULL,
	time_started bigint(20) unsigned NOT NULL DEFAULT 0,
	time_passed int(10) unsigned NOT NULL DEFAULT 0,
	duration int(10) unsigned NOT NULL DEFAULT 0,
	save_later varchar(100) COLLATE utf8_unicode_ci DEFAULT 'yes',
	state varchar(100) COLLATE utf8_unicode_ci NOT NULL,
	params longtext COLLATE utf8_unicode_ci NULL,
	PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}yaexam_exam_sessions ( id varchar(100) COLLATE utf8_unicode_ci NOT NULL, session_value longtext COLLATE utf8_unicode_ci NOT NULL, session_expiry bigint(20) NOT NULL, UNIQUE KEY id (id), PRIMARY KEY  (id)) $collate;
";
		return $tables;
	}
	
	public static function create_pages() 
	{
		include_once( 'admin/yaexam-admin-functions.php' );
		
		$pages = apply_filters( 'yaexam_create_pages', array(
			'archive_exam' => array(
				'name'    => _x( 'exams', 'Page slug', 'yaexam' ),
				'title'   => _x( 'Exams', 'Page title', 'yaexam' ),
				'content' => ''
			),
			'myaccount' => array(
				'name'    => _x( 'yaexam-account', 'Page slug', 'yaexam' ),
				'title'   => _x( 'My Account', 'Page title', 'yaexam' ),
				'content' => '[' . apply_filters( 'yaexam_my_account_shortcode_tag', 'yaexam_my_account' ) . ']'
			),
			'register' => array(
				'name'    => _x( 'yaexam-register', 'Page slug', 'yaexam' ),
				'title'   => _x( 'Register', 'Page title', 'yaexam' ),
				'content' => '[' . apply_filters( 'yaexam_register_shortcode_tag', 'yaexam_register' ) . ']'
			),
			'login' => array(
				'name'    => _x( 'yaexam-login', 'Page slug', 'yaexam' ),
				'title'   => _x( 'Login', 'Page title', 'yaexam' ),
				'content' => '[' . apply_filters( 'yaexam_login_shortcode_tag', 'yaexam_login' ) . ']'
			)
		) );
		
		foreach ( $pages as $key => $page ) {
			yaexam_create_page( esc_sql( $page['name'] ), 'yaexam_' . $key . '_page_id', $page['title'], $page['content'], ! empty( $page['parent'] ) ? em_get_page_id( $page['parent'] ) : '' );
		}
		
		delete_transient( 'yaexam_cache_excluded_uris' );
		
	}
	
	private static function create_options() {
		// Include settings so that we can run through defaults
		include_once( 'admin/class-yaexam-admin-settings.php' );

		$settings = YAEXAM_Admin_Settings::get_settings_pages();
		
		foreach ( $settings as $section ) {
			if ( ! method_exists( $section, 'get_settings' ) ) {
				continue;
			}
			$subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

			foreach ( $subsections as $subsection ) {
				foreach ( $section->get_settings( $subsection ) as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
		}
	}
}

YAEXAM_Install::init();