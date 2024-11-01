<?php

namespace YaExam;

use YaExam\Admin\YAEXAM_Admin_Questions;
use YaExam\Tables\YAEXAM_Table_Result;
use YaExam\Tables\YAEXAM_Table_Question;
class YAEXAM_Ajax {
		
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax'), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'do_yaexam_ajax' ), 0 );
		self::add_ajax_events();
	}
	
	public static function define_ajax() {
		if ( ! empty( $_GET['yaexam-ajax'] ) ) {
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}
			if ( ! defined( 'YAEXAM_DOING_AJAX' ) ) {
				define( 'YAEXAM_DOING_AJAX', true );
			}
			
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 );
			}
			
			$GLOBALS['wpdb']->hide_errors();
			
			nocache_headers();
		}
	}
	

	private static function yaexam_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}

	public static function do_yaexam_ajax() {
		global $wp_query;

		if ( ! empty( $_GET['yaexam-ajax'] ) ) {
			$wp_query->set( 'yaexam-ajax', sanitize_text_field( $_GET['yaexam-ajax'] ) );
		}

		if ( $action = $wp_query->get( 'yaexam-ajax' ) ) {
			self::yaexam_ajax_headers();
			do_action( 'yaexam_ajax_' . sanitize_text_field( $action ) );
			die();
		}
	}

	public static function add_ajax_events() {
		$ajax_events = array(
			'admin_question'							=>	true,
			'admin_category'							=>	true,
			'admin_exam_question'						=>	true,
			'admin_import_question'						=>	true,
			'metabox_data' 								=>	true,
			'metabox_test_data_fixed_questions' 		=>	true,
			'load_questions' 							=>	true,
			'update_question'							=>	true,
			'init_exam' 								=>	true,
			'finish_exam' 								=>	true,
			'save_later_exam'							=>	true,
			'search_exams'								=>	true,
			'get_user_ranking'							=>	true,
		);
		
		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_yaexam_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_yaexam_' . $ajax_event, array( __CLASS__, $ajax_event ) );
				
				// AJAX can be used for frontend ajax requests
				add_action( 'yaexam_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	public static function search_exams() {
		
		$search 	= 	wp_unslash($_GET['term']);
		$limit		=	absint( $_GET['limit'] );
		$exclude	=	$_GET['exclude'];

		$publish_for = 2;

		if( !is_array($exclude) ){
			$exclude = array( $exclude );
		}
		
		$items 	=	get_posts(array(
			'post_type'			=>	'exam',
			'post_status'		=> 'publish',
			'posts_per_page'	=>	$limit,
			's' => $search,
			'exclude' => $exclude
		));

		$results = array();

		if( $items ) {
			foreach( $items as $item ){

				$results[$item->ID] = $item->post_title;
			}
		}

		echo wp_send_json( $results );
		
		wp_die();
	}

	public static function admin_import_question() {
		
		check_ajax_referer( 'admin_yaexam', 'security' );

		$response = [];

		if (isset($_FILES['question_csv'])) {

			$result = YAEXAM_Admin_Questions::startImporting($_FILES['question_csv']);
			
			$response['status'] =  $result['status'] ? 3 : 4;
			$response['items']  =  $result['items'];
		}

		wp_send_json( $response );
	}

	public static function admin_question() {
		global $wpdb;

		check_ajax_referer( 'admin_yaexam', 'security' );

		$method  =	isset($_POST['method']) ? wp_unslash($_POST['method']) : false;

		$response = [];

		switch ( $method ) {

			case 'index':

				$params = [
					'page' 		=>	(isset($_POST['data']['page']) ? yaexam_clean_int( $_POST['data']['page'] ) : 1),
					'perpage'	=>	(isset($_POST['data']['perpage']) ? yaexam_clean_int( $_POST['data']['perpage'] ) : 10),
					'category'	=>	(isset($_POST['data']['category']) ? yaexam_clean_int( $_POST['data']['category'] ) : 0),
					'orderBy'	=>	(is_array($_POST['data']['orderBy']) ? $_POST['data']['orderBy'] : []),
				];
				
				$response = yaexam_get_paginated_question($params);

			break;

			case 'add':
				
				$type  =	isset($_POST['data']['type']) ? yaexam_clean(wp_unslash($_POST['data']['type'])) : 'single';
				
				$table = new YAEXAM_Table_Question();

				$data = [
					'category_id' => 0,
					'title' => $type,
					'content' => '',
					'score' => 1,
					'video' => '',
					'answer_type' => $type,
					'explanation' => '',
					'answers' => json_encode([]),
					'params' => json_encode( [], JSON_UNESCAPED_SLASHES ),
				];

				$question_id = $table->save($data);

				$response = ['id' => $question_id];

			break;
		}

		wp_send_json( $response );
	}
	
	public static function admin_category() {
		global $wpdb;

		check_ajax_referer( 'admin_yaexam', 'security' );

		$method  =	isset($_POST['method']) ? wp_unslash($_POST['method']) : false;

		$response = [];

		switch ( $method ) {

			case 'index':
				
				$response = yaexam_get_question_categories();

			break;
		}

		wp_send_json( $response );
	}

	public static function admin_exam_question() {
		global $wpdb;

		check_ajax_referer( 'admin_yaexam', 'security' );

		$method  =	isset($_POST['method']) ? wp_unslash($_POST['method']) : false;

		$response = [];

		switch ( $method ) {

			case 'assignQuestions':
				
				if( !empty($_POST['data']['questions']) ) {

					$exam_id 	= 	absint($_POST['data']['id']);
					$questions 	= 	$_POST['data']['questions'];

					foreach( $questions as &$question ) {

						$question['title'] = yaexam_clean(wp_unslash($question['title']));
						$question['id']    = absint($question['id']);
					}

					$response['status'] = yaexam_assign_questions( $exam_id, $questions );
					

				}else{

					$response['status'] = false;
				}

			break;

			case 'assignCategories':
				
				if( !empty($_POST['data']['questions']) ) {

					$exam_id 	= 	absint($_POST['data']['id']);
					$questions 	= 	$_POST['data']['questions'];

					foreach( $questions as &$question ) {

						$question['title'] = yaexam_clean(wp_unslash($question['title']));
						$question['id']    = absint($question['id']);
					}

					$response['status'] = yaexam_assign_categories( $exam_id, $questions );

				}else{

					$response['status'] = false;
				}

			break;

			case 'unassign':
				
				if( !empty($_POST['data']['questions']) ) {

					$questions 	= 	yaexam_clean_int($_POST['data']['questions']);
					
					$response['status'] = yaexam_unassign_questions($questions);
					
				}else{

					$response['status'] = false;
				}

			break;

			case 'sort':
				
				$questions 	= 	is_array($_POST['data']['questions']) ? $_POST['data']['questions'] : [];
				$exam_id 	= 	yaexam_clean_int($_POST['data']['id']);

				$filteredQuestions = [];

				if( !empty($questions) ) {
					foreach( $questions as $key => $q ) {
				
						$index = str_replace('q_', '', yaexam_clean(wp_unslash($key)));
			
						$filteredQuestions[yaexam_clean_int($q)] = yaexam_clean_int($index);
					}

					$response['status'] = yaexam_sort_questions($exam_id, $filteredQuestions);
				}else{
					$response['status'] = false;
				}

			break;

			case 'update':
				
				$id 		= 	yaexam_clean_int($_POST['data']['id']);

				$q = yaexam_admin_get_exam_question($id);

				$data = [
					'title'		=>	yaexam_clean(wp_unslash($_POST['data']['title']))
				];
				
				if( $_POST['data']['type'] == 'category' ) {
					$data['question_params'] = $q['question_params'];

					$data['question_params']['total'] = isset($_POST['data']['total']) ? yaexam_clean_int($_POST['data']['total']) : 1;

					$data['question_params'] = json_encode($data['question_params']);
				}

				$response['status'] =  yaexam_update_exam_question($id, $data, ['%s', '%s']);

			break;

			default: 
				
				$response['items'] = yaexam_admin_get_exam_questions(yaexam_clean_int($_POST['data']['id']));

			break;
		}

		wp_send_json( $response );
	}

	public static function metabox_data(){

		check_ajax_referer( 'admin_yaexam', 'security' );

		$method = isset($_POST['method']) ? wp_unslash($_POST['method']) : 'index';

		$response = array();

		switch ( $method ) {

			case 'search_questions':

				$search = isset($_POST['data']['search']) ? yaexam_clean(wp_unslash($_POST['data']['search'])) : '';

				$exclude = isset($_POST['data']['exclude']) ? yaexam_clean_int($_POST['data']['exclude']) : [];

				$items 	=	yaexam_get_questions(
					[
						's' => $search,
						'exclude' => $exclude
					]
				);

				$response['items'] = $items;

			break;

			case 'search_categories':

				$search = isset($_POST['data']['search']) ? yaexam_clean(wp_unslash($_POST['data']['search'])) : '';

				$exclude = isset($_POST['data']['exclude']) ? yaexam_clean_int($_POST['data']['exclude']) : [];

				$items 	=	yaexam_get_fixed_categories(
					[
						's' => $search,
						'exclude' => $exclude
					]
				);

				$response['items'] = $items;

			break;

		}

		wp_send_json( $response );
	}

	public static function init_exam(){

		check_ajax_referer( 'frontend_yaexam', 'security' );

		$sid 		= isset($_GET['sid']) ? yaexam_clean(wp_unslash($_GET['sid'])) : 0;
		
		$session    = 	yaexam_get_user_session($sid);
				
		$response = ['status' => 0];

		if( $session ) {

			$user_id  = get_current_user_id();

			$exam_id  = $session['exam_id'];

			$settings = $session['params']['settings'];
			
			$response = $settings;
			$response['status'] = 1;
		}
		
		wp_send_json( $response );
	}

	public static function load_questions(){

		check_ajax_referer( 'frontend_yaexam', 'security' );

		$sid 		= isset($_GET['sid']) ? yaexam_clean(wp_unslash($_GET['sid'])) : 0;
		$page 		= isset($_GET['page']) ? yaexam_clean_int($_GET['page']) : 1;

		$doingExam	=	new YAEXAM_Doing();
			
		$response = [
			'html' => [
				'props'		=>	['child'],
				'template' 	=> $doingExam->doing_question($sid, $page )
			],
		];

		wp_send_json( $response );
	}

	public static function update_question() {
		
		check_ajax_referer( 'frontend_yaexam', 'security' );

		$sid 		= isset($_POST['data']['sid']) ? yaexam_clean(wp_unslash($_POST['sid'])) : 0;
		$page 		= isset($_POST['data']['page']) ? yaexam_clean_int($_POST['data']['page']) : 1;
		$answered 	= isset($_POST['data']['answered']) ? yaexam_clean(wp_unslash($_POST['data']['answered'])) : '';

		$doingExam	=	new YAEXAM_Doing();
		$doingExam->update_question($sid, $page, $answered);

		wp_send_json( ['status' => 1] );
	}

	public static function finish_exam(){
		
		check_ajax_referer( 'frontend_yaexam', 'security' );
		
		$sid 	= isset($_POST['sid']) ? yaexam_clean(wp_unslash($_POST['sid'])) : 0;		
		
		$response = [];

		$doing_result = yaexam_get_doing_user_result($sid);
		
		if( $doing_result ) {

			$exam_id = $doing_result['exam_id'];
			$result_id = 0;
			$result = [];

			do_action( 'yaexam_before_submit_result', $sid );

			$table = new YAEXAM_Table_Result();

			$result_id = $table->save([
				'exam_id' 				=> $exam_id,
				'user_id' 				=> $doing_result['user_id'],
				'score'	  				=> $doing_result['right_score'],
				'total_score'			=> $doing_result['total_score'],
				'total_corrects'		=> $doing_result['total_corrects'],
				'total_wrongs'			=> $doing_result['total_wrongs'],
				'total_notanswereds'	=> $doing_result['total_notanswereds'],
				'date_start'			=> $doing_result['date_start'],
				'date_end'				=> $doing_result['date_end'],
				'exam_duration'			=> $doing_result['exam_duration'],
				'duration'				=> $doing_result['duration'],
			]);

			$result = yaexam_get_result($result_id);

			$session    = yaexam_get_user_session($sid);
			$settings 	= $session['params']['settings'];

			if( $settings['attempt'] > 0 ) {

				yaexam_exam_update_user_attempt( $exam_id, $session['user_id'], $settings['attempt'] );
			}

			yaexam_remove_user_session( $sid );
			yaexam_remove_save_later_by_exam_id( $doing_result['user_id'], $exam_id );
			yaexam_update_result_id_user_questions( $sid, $result_id );
			
			do_action( 'yaexam_after_submit_result', $result );
			
			$response['link'] = apply_filters( 'yaexam_doing_submit_redirect', 
				yaexam_get_endpoint_url('em-result', $result_id, get_permalink( $exam_id )), $result
			);

			wp_send_json( $response );
		}
	}

	public static function save_later_exam(){
		
		check_ajax_referer( 'frontend_yaexam', 'security' );
		
		$sid 		= isset($_POST['data']['sid']) ? yaexam_clean(wp_unslash($_POST['sid'])) : 0;		
		
		$response = [];

		$user_session = yaexam_get_user_session( $sid );

		if( $user_session['save_later'] == 'yes' ) {

			yaexam_update_user_session( $sid, ['state' => 'save_later'], ['%s'] );

			yaexam_remove_save_later_by_exam_id($user_session['user_id'], $user_session['exam_id'] );

			yaexam_add_save_later([
				'exam_id' 	=> $user_session['exam_id'],
				'user_id'	=>	$user_session['user_id'],
				'session_id' => $user_session['id']
			]);

			$redirect = yaexam_get_endpoint_url( 'view-save-laters', '', yaexam_get_page_permalink( 'myaccount' ) );

			$response['status']   = 1;
			$response['redirect'] = $redirect;
		}

		wp_send_json( $response );
	}

	public static function get_user_ranking(){

		check_ajax_referer( 'frontend_yaexam', 'security' );

		$exam_id 		= 	isset($_POST['data']['exam_id']) ? yaexam_clean_int($_POST['data']['exam_id']) : 0;	
		$user_id		=	get_current_user_id();	

		$total = yaexam_get_users_exam($exam_id);

		$total = is_array($total) ? count($total) : 0;

		wp_send_json( [
			'total'			=>	$total,
			'rankings'		=>	yaexam_get_exam_rankings($exam_id),
			'user_ranking' 	=>  yaexam_get_exam_ranking_of_user( $user_id, $exam_id )
		] );
	}

	
}

YAEXAM_Ajax::init();
