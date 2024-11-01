<?php 

namespace YaExam;

defined( 'ABSPATH' ) || exit;

class YAEXAM_Exam_Session {

	protected $_table = '';

	protected $_data = array();

	protected $_dirty = false;

	private $_session_id;

	private $_session_expiration;

	public function __construct() {

		global $wpdb;

		$this->_table = $wpdb->prefix . 'yaexam_exam_sessions';

		$this->_session_id = $this->has_session();

		$this->_session_expiration = yaexam_time() + intval( apply_filters( 'yaexam_session_expiring', 60 * 60 * 47 ) );

		if( !$this->_session_id ) {

			$this->_session_id = session_id();

			$wpdb->insert($wpdb->prefix . 'yaexam_exam_sessions', array(
				'id' => $this->_session_id,
				'session_expiry' => $this->_session_expiration
			), array('%s'));

		}else{

			$this->_data = $this->get_session_data();

			$wpdb->update($wpdb->prefix . 'yaexam_exam_sessions', array(
				'session_expiry' => $this->_session_expiration
			), array( 'id' => $this->_session_id ), array('%s'), array('%s'));
		}

		

		add_action( 'shutdown', array( $this, 'save_data' ), 20 );
	}
	
	public function __get( $key ) {
		return $this->get( $key );
	}
	
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}
	
	public function __isset( $key ) {
		return isset( $this->_data[ sanitize_title( $key ) ] );
	}
	
	public function __unset( $key ) {
		if ( isset( $this->_data[ $key ] ) ) {
			unset( $this->_data[ $key ] );
			$this->_dirty = true;
		}
	}

	public function get( $key, $default = null ) {
		$key = sanitize_key( $key );
		return isset( $this->_data[ $key ] ) ? maybe_unserialize( $this->_data[ $key ] ) : $default;
	}
	
	public function set( $key, $value ) {
		if ( $value !== $this->get( $key ) ) {
			$this->_data[ sanitize_key( $key ) ] = maybe_serialize( $value );
			$this->_dirty = true;
		}
	}

	public function has_session() {

		global $wpdb;

		$id = session_id();

		if( !$id ) {

			session_start();

			$id = session_id();
		}

		return $wpdb->get_var( 'SELECT id FROM ' . $wpdb->prefix . "yaexam_exam_sessions WHERE id = '{$id}'" );
	}
	
	public function save_data() {
		// Dirty if something changed - prevents saving nothing new
		if ( $this->_dirty && $this->has_session() ) {
			global $wpdb;

			$wpdb->update($wpdb->prefix . 'yaexam_exam_sessions', array(
				'session_value' => maybe_serialize( $this->_data )
			), array( 'id' => $this->_session_id ), array('%s'), array('%s'));

			// Mark session clean after saving
			$this->_dirty = false;
		}
	}

	public function get_session_data() {

		global $wpdb;

		return maybe_unserialize($wpdb->get_var( 'SELECT session_value FROM ' . $wpdb->prefix . "yaexam_exam_sessions WHERE id = '{$this->_session_id}'" ));
	}

	public function destroy_session() {

		global $wpdb;

		$wpdb->delete( $this->_table, array(
			'id' => $this->_session_id
		), array('%s'));
		
		$wpdb->query( $wpdb->prepare( "DELETE FROM $this->_table WHERE session_expiry < %d", yaexam_time() ) );
	}

	public function update_instant_answer( $question_id, $answer = false ){

		$session_data	=	$this->get('doing');

		$session_data['instant_answers'][$question_id] = $answer;

		$this->set('doing', $session_data);
	}
}