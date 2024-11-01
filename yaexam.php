<?php
/*
Plugin Name: YaExam
Plugin URI: https://www.yaexam.com
Description: Create Custom Exams Online
Version: 1.0.7
Author: YaExam
Author URI: https://www.yaexam.com

Text Domain: yaexam
Domain Path: /languages
*/

defined('ABSPATH') or die("No script kiddies please!");

$plugin_name	=	'yaexam';

define( 'YAEXAM_SLUG' , 'yaexam' );
define( 'YAEXAM_DIR' , plugin_dir_path( __FILE__ ) );
define( 'YAEXAM_URI' , plugin_dir_url( __FILE__ ) );
define( 'YAEXAM_CACHE_DIR' , plugin_dir_path( __FILE__ ).'/cache' );
define( 'YAEXAM_LIBRARY_DIR' , plugin_dir_path( __FILE__ ).'/includes/library' );
define( 'YAEXAM_URL', get_site_url().'/wp-admin/admin.php?page=yaexam');

use YaExam\YAEXAM_Session_Handler;
use YaExam\YAEXAM_Exam_Factory;
use YaExam\YAEXAM_Shortcodes;
use YaExam\YAEXAM_Emails;
use YaExam\YAEXAM_Autoloader;
use YaExam\YAEXAM_Doing;
use YaExam\YAEXAM_Install;

final class YaExam {
	
	public $version = '1.0.7';
	
	public $exam_factory = null;
	
	protected static $_instance = null;
	
	private $settings;
	
	public static function instance(){
		
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();

			
		}
		return self::$_instance;
	}
	
	private function __construct(){
		
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
		
		do_action( 'yaexam_loaded' );
	}
	
	private function init_hooks() {
		
		new YAEXAM_Autoloader();

		register_activation_hook( __FILE__, array( YAEXAM_Install::class, 'install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'uninstall' ) );
		
		add_action( 'after_setup_theme', array( $this, 'setup_environment' ) );
		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'init', array( YAEXAM_Shortcodes::class, 'init' ) );
		add_action( 'init', array( YAEXAM_Emails::class, 'init_transactional_emails' ) );
		
		
	}
	
	public function uninstall() {
		
		uninstall_plugin( __FILE__ );
	}
	
	public function init(){
		// Before init action.
		do_action( 'before_yaexam_init' );
		
		// Set up localisation.
		$this->load_plugin_textdomain();
		
		$this->exam_factory = new YAEXAM_Exam_Factory();
		
		if ( $this->is_request( 'frontend' ) || $this->is_request( 'cron' ) ) {

			$session_class  = apply_filters( 'yaexam_session_handler', YAEXAM_Session_Handler::class );
			$this->session  = new $session_class();
		}
		
		add_action( 'init', array($this, 'initPlugin') );
		
		// Init action.
		do_action( 'yaexam_init' );
	}
	
	
	public function define_constants() {
		
		$upload_dir = wp_upload_dir();

		$this->define( 'YAEXAM_PLUGIN_FILE', __FILE__ );
		$this->define( 'YAEXAM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'YAEXAM_PLUGIN_DIR' , plugin_dir_path( __FILE__ ) );
		$this->define( 'YAEXAM_VERSION', $this->version );
		$this->define( 'YAEXAM_LOG_DIR', $upload_dir['basedir'] . '/yaexam-logs/' );
		$this->define( 'YAEXAM_SESSION_CACHE_GROUP', 'yaexam_session_id' );
	}
	
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
	
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}
	
	public function includes() {
		
		require_once(__DIR__ . '/vendor/autoload.php');
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		include_once( 'includes/class-yaexam-autoloader.php' );
		include_once( 'includes/yaexam-core-functions.php' );
		include_once( 'includes/yaexam-widget-functions.php' );
		include_once( 'includes/class-yaexam-install.php' );
		include_once( 'includes/tables/class-yaexam-table.php' );
		include_once( 'includes/class-yaexam-error.php' );
		include_once( 'includes/class-yaexam-ajax.php' );

		

		if ( $this->is_request( 'admin' ) ) {
			include_once( 'includes/admin/class-yaexam-admin.php' );
		}
		
		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend_includes();
		}
		
		if ( $this->is_request( 'frontend' ) || $this->is_request( 'cron' ) ) {
			include_once( 'includes/abstracts/abstract-yaexam-session.php' );
			include_once( 'includes/class-yaexam-session-handler.php' );

			include_once( 'includes/class-yaexam-exam-session.php' );
			
		}
		
		$this->query = include( 'includes/class-yaexam-query.php' );
		
		include_once( 'includes/yaexam-question-functions.php' );
		include_once( 'includes/yaexam-exam-functions.php' );

		include_once( 'includes/class-yaexam-post-type-exam.php' );
		include_once( 'includes/class-yaexam-exam.php' );
				
		include_once( 'includes/class-yaexam-exam-factory.php' );
		
		do_action('yaexam_add_addon');
	}
	
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'yaexam' );

		load_textdomain( 'yaexam', WP_LANG_DIR . '/yaexam/yaexam-' . $locale . '.mo' );
		load_plugin_textdomain( 'yaexam', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}
	
	public function frontend_includes() {
		
		include_once( 'includes/yaexam-message-functions.php' );
		include_once( 'includes/yaexam-template-hooks.php' );
		include_once( 'includes/class-yaexam-template-loader.php' );
		
		include_once( 'includes/class-yaexam-shortcodes.php' );
		include_once( 'includes/class-yaexam-doing.php' );
		include_once( 'includes/class-yaexam-form-handler.php' );

		include_once( 'includes/class-yaexam-frontend-scripts.php' );
		
	}
	
	public function initPlugin(){
		ob_start();
	}
	
	public function include_template_functions() {
		include_once( 'includes/yaexam-template-functions.php' );
	}
	
	public function setup_environment() {
		
		$this->define( 'YAEXAM_TEMPLATE_PATH', $this->template_path() );
	}
	
	public function template_path() {
		return apply_filters( 'yaexam_template_path', 'yaexam/' );
	}
	
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}
	
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}
	
	public function doing() {
		
		return new YAEXAM_Doing();
	}

	public function error_handler() {
		return new WP_Error();
	}

	public function init_user_object() {
		return new WP_User();
	}
	
	public function mailer() {
		return YAEXAM_Emails::instance();
	}
}

function YAEXAM(){
	
	return YaExam::instance();
}

$GLOBALS['yaexam'] = YAEXAM();
