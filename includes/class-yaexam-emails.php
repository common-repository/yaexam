<?php

namespace YaExam;

defined( 'ABSPATH' ) || exit;

class YAEXAM_Emails {

	/** @var array Array of email notification classes */
	public $emails;
	
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.1
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'yaexam' ), '1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.1
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'yaexam' ), '1.0' );
	}

	/**
	 * Hook in all transactional emails.
	 */
	public static function init_transactional_emails() {
		$email_actions = apply_filters( 'yaexam_email_actions', array(
			'yaexam_email_new_member',
			'yaexam_email_new_result',
			'yaexam_email_reset_password'
		) );

		foreach ( $email_actions as $action ) {
			add_action( $action, array( __CLASS__, 'send_transactional_email' ), 10, 10 );
		}
	}

	/**
	 * Init the mailer instance and call the notifications for the current filter.
	 * @internal param array $args (default: array())
	 */
	public static function send_transactional_email() {
		self::instance();
		$args = func_get_args();
		
		do_action_ref_array( current_filter() . '_notification', $args );
	}

	/**
	 * Constructor for the email class hooks in all emails that can be sent.
	 *
	 */
	public function __construct() {
		$this->init();

		// Email Header, Footer and content hooks
		add_action( 'yaexam_email_header', array( $this, 'email_header' ) );
		add_action( 'yaexam_email_footer', array( $this, 'email_footer' ) );
		
		add_action( 'yaexam_email_new_member_notification', array( $this, 'new_member' ), 10, 3 );
		add_action( 'yaexam_email_new_result_notification', array( $this, 'new_result' ), 10, 3 );
		add_action( 'yaexam_email_reset_password_notification', array( $this, 'reset_password' ), 10, 3 );
		
		// Let 3rd parties unhook the above via this hook
		do_action( 'yaexam_email', $this );
	}

	/**
	 * Init email classes.
	 */
	public function init() {
		
		
		
	}

	/**
	 * Return the email classes - used in admin to load settings.
	 *
	 * @return array
	 */
	public function get_emails() {
		return $this->emails;
	}

	/**
	 * Get from name for email.
	 *
	 * @return string
	 */
	public function get_from_name() {
		return wp_specialchars_decode( get_option( 'yaexam_email_from_name' ), ENT_QUOTES );
	}

	/**
	 * Get from email address.
	 *
	 * @return string
	 */
	public function get_from_address() {
		return sanitize_email( get_option( 'yaexam_email_from_address' ) );
	}

	/**
	 * Get the email header.
	 *
	 * @param mixed $email_heading heading for the email
	 */
	public function email_header( $email_heading ) {
		yaexam_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
	}

	/**
	 * Get the email footer.
	 */
	public function email_footer() {

		$email_footer = get_option('yaexam_email_setting_footer');

		yaexam_get_template( 'emails/email-footer.php', array( 'email_footer' => $email_footer ) );
	}
	
	public function wrap_message( $email_heading, $message, $plain_text = false ) {
		// Buffer
		ob_start();

		do_action( 'yaexam_email_header', $email_heading );

		echo wpautop( wptexturize( $message ) );

		do_action( 'yaexam_email_footer' );

		// Get contents
		$message = ob_get_clean();

		return $message;
	}
	
	public function send( $to, $subject, $message, $headers = "Content-Type: text/html\r\n", $attachments = "" ) {
		// Send
		
		if( !is_array($headers) ){

			$headers	=	array();
		}
		
		$headers[]	=	"Content-Type: text/html\r\n";
		
		$from_name		=	get_option('yaexam_email_from_name');
		$from_address	=	get_option('yaexam_email_from_address');
		
		if(!$from_name){
			
			$from_name	=	get_bloginfo();
		}
		
		if(!$from_address || !is_email($from_address)){
			
			$from_address	=	get_option('admin_email');
		}
		
		$headers[]	=	sprintf('From: %s <%s>', $from_name, $from_address);
			
		wp_mail( $to, $subject, $message, $headers, $attachments );
	}
	
	public function new_member( $user_id ) {
		
		if( !$user_id ) return false;
		
		$user			=	get_user_by('id', $user_id);
		
		$to				=	$user->user_email;
		
		$subject		=	get_option('yaexam_email_setting_user_registered_subject');
		$email_heading	=	get_option('yaexam_email_setting_user_registered_header');
		$content  		= 	get_option('yaexam_email_setting_user_registered_content');
		
		$email_content	=	$this->get_content('emails/email-body.php', array('content' => $content));
		
		$patterns	=	array( 
			'user_name' 	=>  $user->user_login,
			'site_url'		=>	get_site_url(),
			'site_name'		=>	get_bloginfo()
		);

		foreach( $patterns as $pattern => $value ) {
			
			$pattern	=	'[{' . $pattern . '}]';
			
			$subject		=	str_replace( $pattern, $value, $subject );
			$email_heading	=	str_replace( $pattern, $value, $email_heading );
			$email_content	=	str_replace( $pattern, $value, $email_content );
		}
		
		$message	=	$this->wrap_message( $email_heading, $email_content );
		
		$this->send( $to, $subject, $message );
	}
	
	public function new_result( $result ) {
		
		if( !$result ) return false;

		$headers	=	array();
		
		$user	= 	get_user_by('id', $result['user_id']);
		$exam   =	new YAEXAM_Exam($result['exam_id']);

		if( !$exam ) return false;

		$settings = $exam->get_settings();

		if( $settings['email_user_result'] == 'no' ) return false;

		$user_email		=	$user->user_email;
		$user_nicename	=	$user->user_nicename;
		
		$to = array( $user_email );
		
		$subject		=	get_option('yaexam_email_setting_new_result_subject');
		$email_heading	=	get_option('yaexam_email_setting_new_result_header');
		$admin_email 	= 	get_option('yaexam_email_notification_new_result_recipient');
		$content  		= 	get_option('yaexam_email_setting_new_result_content');

		if( $admin_email ) {

			$headers[] = 'Cc:' . $admin_email;
		}
		
		$email_content	=	$this->get_content('emails/email-body.php', array('content' => $content));
		
		$patterns	=	array( 
			'site_url'		=>	get_site_url(),
			'site_name'		=>	get_bloginfo(),
			'exam_title' 	=>  $exam->get_title(),
			'examl_link'	=>	$exam->get_permalink(),	
			'duration'		=>	$result['duration'],
			'score'			=>	$result['score'],
			'total_score'	=>	$result['total_score'],
			'date'			=>	$result['date_start'],
			'user_nicename'	=>	$user_nicename,
			'user_email'    =>  $user_email,
		);

		foreach( $patterns as $pattern => $value ) {
			
			$pattern	=	'[{' . $pattern . '}]';
			
			$subject		=	str_replace( $pattern, $value, $subject );
			$email_heading	=	str_replace( $pattern, $value, $email_heading );
			$email_content	=	str_replace( $pattern, $value, $email_content );
		}
		
		$message	=	$this->wrap_message( $email_heading, $email_content );
		
		$this->send( $to, $subject, $message, $headers );
	}
	
	public function reset_password( $user_login = '', $reset_key = '' ) {
		
		if ( $user_login && $reset_key ) {
			$user			=	get_user_by( 'login', $user_login );

			$to				=	stripslashes( $user->user_email );
			
			$subject		=	get_option('yaexam_email_setting_reset_password_subject');
			$email_heading	=	get_option('yaexam_email_setting_reset_password_header');
			$content  		= 	get_option('yaexam_email_setting_reset_password_content');
			
			$link_reset		=	esc_url( add_query_arg( array( 'key' => $reset_key, 'login' => rawurlencode( $user_login ) ), yaexam_get_endpoint_url( 'em-lost-password', '', yaexam_get_page_permalink( 'myaccount' ) ) ) );
			
			$email_content	=	$this->get_content('emails/email-body.php', array('content' => $content));
			
			$patterns	=	array( 
				'site_url'		=>	get_site_url(),
				'site_name'		=>	get_bloginfo(),
				'user_login' 	=>  $user_login,
				'link_reset'	=>	$link_reset,	
			);
	
			foreach( $patterns as $pattern => $value ) {
				
				$pattern	=	'[{' . $pattern . '}]';
				
				$subject		=	str_replace( $pattern, $value, $subject );
				$email_heading	=	str_replace( $pattern, $value, $email_heading );
				$email_content	=	str_replace( $pattern, $value, $email_content );
			}

			$message	=	$this->wrap_message( $email_heading, $email_content );
			
			$this->send( $to, $subject, $message );
		}
	}
	
	public function get_content( $template, $args = array() ) {
		
		ob_start();
		
		yaexam_get_template( $template, $args );
		
		return ob_get_clean();
	}
	
}
