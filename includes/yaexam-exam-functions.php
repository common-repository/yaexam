<?php 

if ( ! defined( 'ABSPATH' ) ) { exit; }

use YaExam\Tables\YAEXAM_Table_Exam_Result;

function yaexam_exam_get_settings( $post_id )
{	

	$metadata = get_metadata('post', $post_id);

	$settings	=	apply_filters( 'exam_get_default_settings', [
			'attempt'				=>	0,
			'duration'				=>	0,
			'publish_for' 			=>	2,
			'show_result' 			=>	'yes',
			'show_ranking' 			=>	'yes',
			'shuffle_questions' 	=>	'yes',
			'shuffle_answers' 		=>	'yes',
			'save_later'			=>	'no',
			'email_user_result'		=>	'yes'
		], $post_id);
	
	$results = [];

	foreach( $settings as $name => $default_value ) {

		if( isset($metadata['_' . $name]) ) {
			$results[$name] = $metadata['_' . $name][0];
		}else{
			$results[$name] = $default_value;
		}
	}

	return $results;
}

function yaexam_exam_set_settings( $post_id, $key, $value )
{
	$settings 	=	get_post_meta( $post_id, '_settings', true );
	
	$settings	=	$settings ? $settings : array();
	
	$settings	=	wp_parse_args($settings, array(
		'display_perpage'		=>	10,
		'duration'				=>	20,
		'publish_for'			=>	0,
		'attempt'				=>	0,
		'assigned_users'		=>	array(),
		'assigned_groups'		=>	array(),
		'memberships'			=>	array(),
		'played'				=>	0,
		'is_reviews'			=>	1,
		'is_email_result'		=>	1,
		'is_pagination'			=>	1,
		'is_backward'			=>	1,
		'is_sidebar_tracking'	=>	1,
		'is_timeout_answer'		=>	0,
		'ranking'				=>	array(),
		'adaptive_times'		=>	3,
		'adaptive_max_round'	=>	3,
		'is_question_report'	=>	0,
		'save_for_later'		=>	0,
		'auto_save'				=>	0,
		'play_all'				=>	0,
		'show_result'			=>	1,
	));
	
	$settings[$key]	=	$value;
	
	return update_post_meta( $post_id, '_settings', $settings );
}

function yaexam_get_exam( $the_exam, $args = array() ) {
	
	return YAEXAM()->exam_factory->get_exam( $the_exam, $args );
}

function yaexam_get_exams( $args = array() ) {
	
	$args	=	wp_parse_args( $args, array(
		'post_type'			=>	'exam',
		'posts_per_page'	=>	-1,
		'meta_query'     => array(
			array( 'key' => '_publish_for', 'value' => [2, 4], 'meta_compare' => 'IN' )
		)
	));
	
	return get_posts( $args );
}

function yaexam_get_doing_questions( $session_data = array(), $is_shuffle_answers = false )
{

	$question_ids 	 = $session_data['ids'];
	$question_params = isset($session_data['question_params']) ? $session_data['question_params'] : array();
	
	$questions	=	get_posts(array(
		'post_type'			=>	'question',
		'post_status'		=>	'publish',
		'suppress_filters' 	=>	true,
		'orderby' 			=> 'post__in',
		'numberposts'		=>	-1,
		'include'			=>	$question_ids,
	));

	if($questions){

		foreach($questions as &$question)
		{	
			$question->score		=	yaexam_get_post_meta( $question->ID, 'score' );
			$question->answer_type	=	yaexam_get_post_meta( $question->ID, 'answer-type' );
			$question->answers		=	yaexam_get_post_meta( $question->ID, 'answers' );
			$question->order_type	=	yaexam_get_post_meta( $question->ID, 'order_type' );
			$question->explanation	=	yaexam_get_post_meta( $question->ID, 'explanation' );
			$question->timeout		=	yaexam_get_post_meta( $question->ID, 'timeout' );
			
			$question->params 		=	$question_params;

			$params_answer 			= 'params_' . $question->answer_type;

			$question->$params_answer 	=	yaexam_get_post_meta( $question->ID, $params_answer );

			if( $question->answers && $is_shuffle_answers ) {
				
				if( is_array($question->answers) ) {
					
					if( $question->answer_type != 'order' && $question->answer_type != 'guess_word'  ){
						uksort( $question->answers, function() { return rand() > getrandmax() / 2; } );
					}
				}
			}
		}
	}
	
	return $questions;
}

function yaexam_get_rankings( $exam_id, $args = array() )
{
	global $wpdb;
	
	$args	=	wp_parse_args($args, array( 'count' => 10 ));
	
	$result_tbl	=	$wpdb->prefix . 'yaexam_results';
	
	$rankings	=	 $wpdb->get_results($wpdb->prepare(
						'SELECT MAX(score) AS score, total_score, user_id FROM ' .
						$result_tbl . ' WHERE exam_id = %d GROUP BY user_id ORDER BY score DESC LIMIT %d',
						yaexam_clean_int($exam_id), $args['count']),
					ARRAY_A);
	
	if($rankings){
		
		foreach($rankings as &$r){
			if($r['user_id']){
				$user	=	new WP_User($r['user_id']);
				
				$r['user']	=	$user->user_nicename;
			}
		}
	}
	
	return $rankings;
}

function yaexam_exam_remove_assigned_users( $post_id, $ids_remove = array() ) {
	
	if( !$ids_remove ) {
		return false;
	}
	
	if( !is_array($ids_remove) ) {
		$ids_remove	=	array($ids_remove);
	}
	
	$settings 	=	get_post_meta($post_id, '_exam_settings', true);
	
	$settings	=	$settings ? $settings : array();
	
	if( isset($settings['assigned_users']) && $settings['assigned_users'] ){
		
		$ids_save	=	array_diff($settings['assigned_users'], $ids_remove);
		
	}
	
	yaexam_exam_set_settings($post_id, 'assigned_users', $ids_save);
	
}

function yaexam_get_result( $result_id ) {
	global $wpdb;
	
	if(!$result_id || !yaexam_clean_int($result_id)) return false;
	
	$result_id	=	yaexam_clean_int($result_id);
	
	$result_tbl	=	$wpdb->prefix . 'yaexam_results';
	
	return $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $result_tbl . ' WHERE id = %d', $result_id), ARRAY_A);
}

function yaexam_get_results2( $exam_id ){

	global $wpdb;
	
	$result_tbl	=	$wpdb->prefix . 'yaexam_results';
	
	$results = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $result_tbl . ' WHERE exam_id = %d', $exam_id), ARRAY_A);
	
	return $results;
}

function yaexam_count_results() {

	global $wpdb;

	return $wpdb->get_var('SELECT COUNT(id) AS total FROM ' . $wpdb->prefix . 'yaexam_results');
}

function yaexam_get_results( $args ) {

	global $wpdb;

	$args = wp_parse_args( $args, [
		'id' => false,
		'exam_id' => false,
		'user_id' => false,
		's' => '',
		'include' => [],
		'exclude' => [],
		'limit' => false,
		'offset' => 0,
		'orderby' => 'id',
		'order' => 'DESC',
		'where' => [],
	] );

	$query = 'SELECT * FROM ' . $wpdb->prefix . 'yaexam_results';

	$where = $args['where'];

	if( $args['id'] ) {

		$where[] = $wpdb->prepare('id = %d', yaexam_clean_int($args['id']));
	}

	if( $args['s'] ) {

		$where[] = $wpdb->prepare('title LIKE %s', '%' .yaexam_clean($args['s']) . '%');
	}

	if( $args['include'] ) {

		$include = implode(', ', yaexam_clean_int($args['include']));

		$where[] = 'id IN(' . $include . ')';
	}

	if( $args['exclude'] ) {

		$exclude = implode(', ', yaexam_clean_int($args['exclude']));

		$where[] = 'id NOT IN(' . $exclude . ')';
	}

	if( $args['exam_id'] ) {

		$where[] = $wpdb->prepare('exam_id = %d', yaexam_clean_int($args['exam_id']));
	}

	if( $args['user_id'] ) {

		$where[] = $wpdb->prepare('user_id = %d', yaexam_clean_int($args['user_id']));
	}
	
	if( $where ) {

		$query .= ' WHERE ' . implode( ' AND ', $where );

	}

	if( $args['orderby'] == 'include' && $args['include'] ) {

		$orderby = yaexam_clean_int($args['include']);
		array_unshift($orderby, 'id');
		
		$query .= ' ORDER BY field(' . implode(', ', $orderby) . ')';

	}elseif( $args['orderby'] == 'RAND()' ){

		$query .= ' ORDER BY RAND()';

	}else{

		$query .= ' ORDER BY ' . yaexam_clean($args['orderby']) . ' ' . yaexam_clean($args['order']);
	}

	if( $args['limit'] ) {

		$query .= $wpdb->prepare(' LIMIT %d OFFSET %d', yaexam_clean_int($args['limit']), yaexam_clean_int($args['offset']));
	}



	return $wpdb->get_results( $query, ARRAY_A );
}

function yaexam_get_paginated_results( $params = array() ){
		
	global $wpdb;

	$params = wp_parse_args( $params, array(
		'perpage' 	=> 20, 
		'page' 		=> 1, 
		'order' 	=> 'r.date_start DESC',
		'user_meta' => false
	) );
	
	$result_tbl	=	$wpdb->prefix . 'yaexam_results';
	$user_tbl	=	$wpdb->prefix . 'users';

	$page 		=	yaexam_clean_int($params['page']);
	$perpage	=	yaexam_clean_int($params['perpage']);

	$total 		= 	yaexam_get_total_lastest_results( yaexam_clean_int($params['exam']) );
	
	$pages 		=	ceil($total / $perpage);
	$offset 	=	($page - 1) * $perpage;

	if($page > $pages){
		$page = 1;
	}

	$results = array();
	
	if( $total > 0 ){
		$query	=	$wpdb->prepare(
			'SELECT r.id, r.exam_id, r.user_id, u.user_nicename AS user_name, r.score, r.total_score, r.total_corrects, r.total_wrongs,  r.total_notanswereds, r.exam_duration, r.percent, r.duration, r.date_start, r.date_end, r.others '.
				'FROM ' . $result_tbl . ' r LEFT JOIN ' . $user_tbl . ' u ON u.id = r.user_id '.
				'WHERE r.exam_id = %d AND r.date_start IN(SELECT max(dr.date_start) FROM ' . $result_tbl . ' dr WHERE dr.exam_id = %d GROUP BY dr.user_id) '.
				'GROUP BY r.user_id ORDER BY ' . $params['order'] . ' LIMIT %d, %d'
			, $params['exam'], $params['exam'], $offset, $perpage);
		
		$results	= $wpdb->get_results($query, ARRAY_A);

		if($results){
			
			$users = get_users(['include' => yaexam_clean_int(yaexam_get_values_by_key( $results, 'user_id' ))]);

			foreach( $results as &$r ) {

				foreach( $users as $user ) {

					if( $user->ID == $r['user_id'] ) {

						$r['user_name'] = $user->display_name;
					}
				}

			} 

		}
	}

	return array( 
		'data' 		 =>  $results,
		'total' 	=> $total, 
		'pages' 	=> $pages, 
		'page' 		=> $page
	);
}

function yaexam_get_total_lastest_results( $exam_id ) {
	
	global $wpdb;

	$result_tbl	=	$wpdb->prefix . 'yaexam_results';
	$user_tbl	=	$wpdb->prefix . 'users';

	$exam_id = yaexam_clean_int($exam_id);

	$query  = $wpdb->prepare(
		'SELECT COUNT(*) FROM ' . $result_tbl . ' r LEFT JOIN ' . $user_tbl . ' u ON u.id = r.user_id '.
			'WHERE r.exam_id = %d AND r.date_start IN(SELECT MAX(dr.date_start) FROM ' . $result_tbl . ' dr WHERE dr.exam_id = %d GROUP BY dr.user_id) '.
			'GROUP BY r.user_id', $exam_id, $exam_id);
	
	return $wpdb->get_var($query);
}

function yaexam_remove_results( $ids ) {

	global $wpdb;
	
	$result_tbl	=	$wpdb->prefix . 'yaexam_results';
	$user_question_tbl = $wpdb->prefix . 'yaexam_user_questions';
		
	$ids = implode( ',', yaexam_clean_int($ids) );

	$wpdb->query( "DELETE FROM {$result_tbl} WHERE id IN($ids)" );
	$wpdb->query( "DELETE FROM {$user_question_tbl} WHERE result_id IN($ids)" );

	
}

function yaexam_exam_remove_user_score( $exam_id ) {

	$results = yaexam_get_results( $exam_id );

	if( $results ) {

		foreach( $results as $result ) {

			if( $result['user_id'] ) {

				$user_score = yaexam_get_user_score( $result['user_id'] );

				if( $user_score > 0 ) {

					$new_user_score = yaexam_clean_int($user_score) - yaexam_clean_int($result['score']);

					$new_user_score = $new_user_score > 0 ? $new_user_score : 0;

					yaexam_new_user_score( $new_user_score, $result['user_id'] );
				}
			}
		}
	}
		
}

function yaexam_format_results( $data ) {
	
	if(!$data) return false;
	
	foreach($data as &$result){
		
		if(isset($result['score']) && isset($result['exam_id'])){
			
			$result['exam_title']		=	get_the_title($result['exam_id']);
			$result['exam_admin_link']	=	admin_url('post.php?post=' . $result['exam_id'] . '&action=edit');
			
			
			if(isset($result['user_id']) && $result['user_id']) {
				$user_info	=	get_userdata( $result['user_id'] );
				
				if($user_info) {

				    $first_name = $user_info->first_name;
				    $last_name	= $user_info->last_name;
					
					if( $first_name || $last_name ) {
						$result['user_nicename']	=	$first_name . ' ' . $last_name;
					}

				}else{

					$result['user_nicename'] = esc_html__('Guest', 'yaexam');
				}
			}
		}
		
		if(isset($result['date_start'])){
			
			$result['date_start']	=	yaexam_get_date_formated( $result['date_start'] );
		}

		$result	=	apply_filters( 'yaexam_filters_format_results', $result );
	}
	
	return $data;
}

function yaexam_add_ranking( $exam_id, $data ){
	
	if(!$exam_id || !$data ) return false;
	
	if (is_object($data))
	{
		$data = get_object_vars( $data );
	}
	
	if (!isset($data['min']) || !isset($data['max']) || !$data['name'] ) return false;
	
	if($data['min'] > $data['max']) return false;
	
	if($data['certificate']) {
		$data['certificate']	=	yaexam_clean_int($data['certificate']);
	}
	
	$exam_settings	=	yaexam_exam_get_settings( $exam_id );
	
	$ranking		=	$exam_settings['ranking'];
	
	$is_validated	=	true;
	
	if(!isset($data['id'])){
		
		$data['id']	=	md5(($data['min'] + $data['max']));
		array_push($ranking, $data);
		
	}else{
		
		foreach( $ranking as $index => $r ){
			if($r['id'] == $data['id']){
				
				$ranking[$index] =	$data;
				
			}
		}
	}
	
	foreach($ranking as $r){
	
		$range_ranking	=	range($r['min'], $r['max']);
	
		if( ($r['id'] != $data['id']) && (in_array($data['min'], $range_ranking) || in_array($data['max'], $range_ranking)) ) {
			$is_validated	=	false;
		}
	
	}
	
	if(!$is_validated) {
	
		return false;
	}
	
	yaexam_exam_set_settings($exam_id, 'ranking', $ranking);
	
	return $data['id'];
}

function yaexam_remove_ranking( $exam_id = '', $id = '') {
	if(!$exam_id || !$id) return false;
	
	$exam_settings	=	yaexam_exam_get_settings( $exam_id );
	
	$ranking		=	$exam_settings['ranking'];
	$is_remove		=	false;
	
	foreach( $ranking as $index => $r ){
		if($r['id'] == $id){
			$is_remove	=	true;
			unset($ranking[$index]);
		}
	}
	
	if($is_remove){
		yaexam_exam_set_settings($exam_id, 'ranking', $ranking);
	}
}

function yaexam_get_ranking( $exam_id = '', $id = '' ) {
	
	if(!$exam_id) return false;
	
	$exam_settings	=	yaexam_exam_get_settings( $exam_id );
	
	$ranking		=	$exam_settings['ranking'];
	
	if($ranking){
		
		if($id) {
			foreach( $ranking as $r ){
				if($r['id'] == $id){
					
					return $r;
				}
			}
		}else{
			
			return $ranking;
		}
	}
	
	return false;
}

function yaexam_is_ranking( $exam_id = '', $score = 0, $total_score = 0, $only_name = true ) {
	
	if($total_score < 0) return false;
	
	if($score > 0){

		$percent	=	round(( yaexam_clean_int($score) * 100 ) / yaexam_clean_int($total_score));
	}else{

		$percent 	=	0;
	}
	
	$exam		=	new YAEXAM_Exam( $exam_id );
	
	$ranking	=	yaexam_get_ranking( $exam_id );
	
	if(!$ranking) return false;
	
	foreach($ranking as $r){
		
		if(($percent >= $r['min']) && ($percent <= $r['max'])){
			
			if($only_name){
				return $r['name'];
			}else{
				return $r;
			}
			
		}
	}

	return false;
}

function yaexam_is_user_score( $exam_id ) {

	$is_user_score = get_post_meta( $exam_id, '_user_score', true );

	return $is_user_score;
}

function yaexam_exam_get_publish_for( $id ) {
	
	$publish_for = array(
		0 => __('Every Users', 'yaexam'),
		1 => __('Private', 'yaexam'),
		2 => __('Assigned Users', 'yaexam'),
		3 => __('Assigned Groups', 'yaexam'),
	);
	
	if(isset($id)){
		
		return $publish_for[$id];
		
	}else{
		
		return $publish_for;
	}
}

function yaexam_get_type_examing( $exam_id ) {

	if(!$exam_id) return 'normal';

	$exam_type	=	yaexam_get_post_meta( $exam_id, 'exam_type' );
	
	if( !$exam_type ) {

		return 'normal';
	}

	return $exam_type;
}

function yaexam_exam_get_scores( $ids = array() ){

	global $wpdb;

	if( !$ids || !is_array( $ids ) ){
		return 0;
	}

	$query = "SELECT meta_value FROM $wpdb->postmeta WHERE post_id IN (" . implode(',', $ids) . ") AND meta_key LIKE '_score'";
	
    $scores = $wpdb->get_col( $query );

    if( !empty( $scores ) ) {
    	
    	return array_sum( $scores );

    }else{

    	return 0;
    }

}

function yaexam_get_result_meta( $result_id, $name ) {

	global $wpdb;
	
	$result = YAEXAM_Test::get_result( $result_id, false );

	if( isset($result['others']) ) {

		$result['others'] = json_decode( $result['others'], true );

		if( isset($result['others'][$name]) ) {

			return $result['others'][$name];
		}

	}

	return false;
}

function yaexam_update_result_meta( $result_id, $name, $value ) {

	global $wpdb;

	$result = YAEXAM_Test::get_result( $result_id, false );

	if( isset($result['others']) ) {

		$result['others'] = json_decode( $result['others'], true );

		$result['others'][$name] = $value;

	}else{

		$result['others'] = array( $name => $value );
	}

	$others_query = json_encode( $result['others'] );

	$wpdb->update( $wpdb->prefix . 'yaexam_results', 
		array( 'others' => $others_query ), 
		array( 'id' => $result_id ), array( '%s' ), array( '%d' ) );
}

function yaexam_update_result_cert_id( $result_id, $cert_id = false ) {

	global $wpdb;

	if( !$cert_id ) {

		$cert_id = yaexam_get_random_user_cert_id( $result_id );
		
		$wpdb->update( $wpdb->prefix . 'yaexam_results', 
			array( 'cert_id' => strtoupper($cert_id) ), 
			array( 'id' => $result_id ), array( '%s' ), array( '%d' ) );
	}


}

function yaexam_get_random_user_cert_id( $id ) {

	$count = strlen($id);

	$certid = substr(uniqid(), 0, (10 - $count)) . $id;

	return $certid;
}

function yaexam_get_session_result( $exam_id ) {
		
	$session    = 	new YAEXAM_Exam_Session();
	
	$doing_data = $session->get('doing');
	
	$result = $session->get('result_' . $exam_id);
	
	$result['questions'] = $doing_data['questions'];
	
	return $result;
}

function yaexam_exam_update_user_attempt( $exam_id, $user_id, $exam_attempts ) {

	global $wpdb;
	
	$user_attempts = yaexam_exam_get_user_attempt( $exam_id, $user_id );

	if( $exam_attempts > 0 ) {

		$table = new YAEXAM_Table_Exam_Result();

		if( $user_attempts && ($exam_attempts > $user_attempts['user_attempts']) ) {
			
			$attempt = $user_attempts['user_attempts'] + 1;

			$table->save([
				'id' => $user_attempts['id'],
				'user_attempts' => $attempt
			]);
		}else{

			$table->save([
				'user_attempts' => 1,
				'user_id' => $user_id,
				'exam_id' => $exam_id
			]);
		}
	}
}

function yaexam_exam_get_user_attempt( $exam_id, $user_id ) {

	global $wpdb;

	return $wpdb->get_row(
		$wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'yaexam_exam_results' . ' WHERE user_id = %d AND exam_id = %d', $user_id, $exam_id), ARRAY_A);
	
}

function yaexam_exam_remove_user_attempt( $exam_id, $user_id ) {

	global $wpdb;

	$wpdb->delete($wpdb->prefix . 'yaexam_exam_results', [
		'exam_id' => $exam_id,
		'user_id' => $user_id,
	], ['%d', '%d']);
}

function yaexam_exam_get_user_attempt_by_id( $exam_id, $user_id, $attempt ) {

	global $wpdb;

	$wpdb->update($wpdb->prefix . 'yaexam_exam_results', [
		'user_attempts' => $attempt
	], [
		'exam_id' => $exam_id,
		'user_id' => $user_id,
	], ['%d'], ['%d', '%d']);

}

function yaexam_get_exam_category_questions( $category, $params ) {

	global $wpdb;

	$limit = yaexam_clean_int($params['total']);

	return $wpdb->get_col(
		$wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'yaexam_questions' . ' WHERE category_id = %d LIMIT %d', $category, $limit)
	);
}

function yaexam_get_exam_questions( $exam_id ) {

	global $wpdb;

	$questions = [];

	$exam_questions = $wpdb->get_results(
		$wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'yaexam_exam_questions' . ' WHERE exam_id = %d ORDER BY question_order ASC', $exam_id), ARRAY_A
	);

	if( $exam_questions ) {
		foreach( $exam_questions as $q ) {

			if( $q['question_type'] == 'question' ) {
				
				$questions[] = $q['question_id'];

			}elseif( $q['question_type'] == 'category' ){

				$question_params = json_decode( $q['question_params'], true );
				$questions = array_merge( $questions, yaexam_get_exam_category_questions($q['question_id'], $question_params) );	
			}
		}
	}

	return apply_filters('yaexam_get_exam_questions', $questions, $exam_id);
}

function yaexam_get_exam_rankings( $exam_id, $args = array() ) {

	global $wpdb;

	$params = wp_parse_args($args, ['limit' => 5]);
	
	$user_table = $wpdb->prefix . 'users';
	$result_table = $wpdb->prefix . 'yaexam_results';
	
	$results = $wpdb->get_results(
		$wpdb->prepare('SELECT r.*, u.user_nicename AS user_name FROM ' . $wpdb->prefix . 'yaexam_results r' 
			. " LEFT JOIN {$user_table} u ON u.ID = r.user_id WHERE exam_id = %d AND r.score IN (SELECT MAX(r2.score) FROM {$result_table} r2 WHERE exam_id = %d GROUP BY r2.user_id) GROUP BY r.user_id ORDER BY score DESC, duration ASC LIMIT %d", 
			$exam_id, $exam_id, $params['limit']), ARRAY_A);

	if( $results ) {
		
	}

	return apply_filters('yaexam_get_exam_ranking', $results, $exam_id);
}

function yaexam_get_exam_ranking_of_user( $user_id, $exam_id ) {

	global $wpdb;

	$table = $wpdb->prefix . 'yaexam_results';

	$query = $wpdb->prepare("SELECT num, user_id, score FROM ( SELECT (@row_number:=@row_number + 1) AS num, user_id, score, exam_id FROM {$table} WHERE exam_id = %d ORDER BY score DESC ) t WHERE t.user_id = %d AND t.exam_id = %d ORDER BY t.score DESC", 
	$exam_id, $user_id, $exam_id);
	
	$wpdb->query('SET @row_number = 0;');

	return $wpdb->get_row( $query, ARRAY_A );
}

function yaexam_get_users_exam( $exam_id ) {

	global $wpdb;

	$table = $wpdb->prefix . 'yaexam_results';

	return $wpdb->get_results($wpdb->prepare("SELECT DISTINCT user_id FROM {$table} WHERE exam_id = %d", $exam_id), ARRAY_A);
}

function yaexam_get_user_question_by_page( $session_id, $page ) {

	global $wpdb;

	$result = $wpdb->get_row(
		$wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'yaexam_user_questions WHERE session_id = %s AND question_order = %d', $session_id, $page), ARRAY_A);
	
	if( $result ) {
		$question = maybe_unserialize($result['question_data']);
		
		return $question;
	}

	return [];
}

function yaexam_get_user_question_by_session( $session_id ) {

	global $wpdb;

	$results = $wpdb->get_results(
		$wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'yaexam_user_questions WHERE session_id = %s', $session_id), ARRAY_A);

	if( $results ) {

		foreach( $results as &$result ) {
			$result['question_data'] = maybe_unserialize($result['question_data']);
			$result['question_answered'] = maybe_unserialize($result['question_answered']);
		}

		return $results;
	}

	return [];
}

function yaexam_get_question_by_ids( $ids ) {

	global $wpdb;
	
	if( !$ids ) return [];
	
	$where = 'id IN (' . implode(', ', array_unique($ids)) . ')';

	$results = $wpdb->get_results(
		'SELECT * FROM ' . $wpdb->prefix . 'yaexam_questions' . ' WHERE ' . $where, ARRAY_A
	);

	$questions = [];

	if( $results ) {
		foreach( $results as $r ) {
			$questions['q_' . $r['id']] = $r;
		}
	}

	return $questions;
}

function yaexam_get_user_session( $sid ) {

	global $wpdb;

	$result = $wpdb->get_row($wpdb->prepare(
		'SELECT * FROM ' . $wpdb->prefix . 'yaexam_user_sessions WHERE id = %s', $sid), ARRAY_A);

	$result['params'] = maybe_unserialize($result['params']);

	return $result;
}

function yaexam_create_user_session( $data ) {

	global $wpdb;

	$sid = \Ramsey\Uuid\Uuid::uuid4()->toString();

	$params = is_array($data['params']) ? $data['params'] : [];

	$params = wp_parse_args($params, ['settings' => yaexam_exam_get_settings($data['exam_id'])]);
	
	$wpdb->insert($wpdb->prefix . 'yaexam_user_sessions',
		[
			'id'			=>	$sid,
			'user_id' 		=>  $data['user_id'],
			'exam_id'		=>	$data['exam_id'],
			'state'			=>	$data['state'],
			'time_started' 	=>  yaexam_clean_int($data['time_started']),
			'time_passed'  	=>  $data['time_passed'],
			'duration'		=>	$data['duration'],
			'save_later'	=>	$data['save_later'],
			'params'		=>	maybe_serialize($params)
		], 
		['%s', '%d', '%d', '%s', '%d', '%d', '%d', '%s', '%s']);
	
	return $sid;
}

function yaexam_remove_user_session( $id ) {

	global $wpdb;

	$wpdb->delete($wpdb->prefix . 'yaexam_user_sessions', ['id' => $id], ['%s']);
}

function yaexam_remove_user_session_when_doing( $user_id, $exam_id ) {

	global $wpdb;

	$wpdb->delete($wpdb->prefix . 'yaexam_user_sessions', ['exam_id' => $exam_id, 'user_id' => $user_id], ['%d', '%d']);
}

function yaexam_update_user_session( $id, $data, $formats ) {

	global $wpdb;

	$wpdb->update($wpdb->prefix . 'yaexam_user_sessions', $data, ['id' => $id], $formats, ['%s']);
}

function yaexam_is_valid_session( $session_id ) {

	global $wpdb;

	$current_session = $wpdb->get_row($wpdb->prepare(
		'SELECT * FROM ' . $wpdb->prefix . 'yaexam_user_sessions WHERE id = %s', $session_id), ARRAY_A);
	
	if( $current_session ) {

		if( $current_session['state'] == 'doing' ) {

			$start_date = new DateTime(strtotime($current_session['time_started']));

			$since_start = $start_date->diff(new DateTime('NOW'));

			if( $current_session['duration'] > 0 ) {

				if($since_start->i < yaexam_clean_int($current_session['duration'])) {
					return true;
				}else{
					return false;
				}

			}else{

				return true;

			}

		}elseif( $current_session['save_later'] == 'yes' && $current_session['state'] == 'save_later' ) {

			return true;
		}

	}else{

		return false;
	}
}

function yaexam_init_user_session( $data ) {

	global $wpdb;

	$current_session = $wpdb->get_row($wpdb->prepare(
		'SELECT * FROM ' . $wpdb->prefix . 'yaexam_user_sessions WHERE user_id = %d AND exam_id = %d', $data['user_id'], $data['exam_id']), ARRAY_A);
	
	if( $current_session ) {

		if( $current_session['state'] == 'doing' ) {

			$start_date = new DateTime(strtotime($current_session['time_started']));

			$since_start = $start_date->diff(new DateTime('NOW'));

			if( $current_session['duration'] > 0 ) {

				if($since_start->i < yaexam_clean_int($current_session['duration'])) {
					return $current_session['id'];
				}else{
					return false;
				}

			}else{

				return $current_session['id'];

			}

		}elseif( $current_session['save_later'] == 'yes' && $current_session['state'] == 'save_later' ) {

			yaexam_update_user_session( $current_session['id'], ['state' => 'doing'], ['%s'] );

			return $current_session['id'];
		}

	}else{
		
		$session_id = yaexam_create_user_session([
			'user_id' 		=>  $data['user_id'],
			'exam_id'		=>	$data['exam_id'],
			'state'			=>	$data['state'],
			'time_started' 	=>  $data['time_started'],
			'time_passed'  	=>  $data['time_passed'],
			'duration'		=>	$data['duration'],
			'save_later'	=>	$data['save_later'],
			'params'		=>	$data['params']
		]);

		yaexam_create_snapshot_user_questions( [
			'session_id' 	=> 	$session_id,
			'user_id' 		=>  $data['user_id'],
			'exam_id'		=>	$data['exam_id'],
			'questions'		=>	$data['questions']
		] );

		return $session_id;
	}
	
}

function yaexam_create_snapshot_user_questions( $params ) {

	global $wpdb;

	$session_id = $params['session_id'];
	$questions  = $params['questions'];

	$query_data = [];

	foreach( $questions as $index => $question ) {

		$question_data = maybe_serialize($question);

		$tmp = [
			$params['user_id'], 
			$params['exam_id'], 
			$question['id'],
			"'{$question_data}'",
			"'{$session_id}'",
			($index + 1)
		];

		$query_data[] = '(' . implode(', ', $tmp) . ')';
	}

	$query_data = implode(', ', $query_data);

	$wpdb->query('INSERT INTO ' . $wpdb->prefix . 'yaexam_user_questions(user_id, exam_id, question_id, question_data, session_id, question_order) VALUES ' . $query_data);
}

function yaexam_remove_snapshot_user_questions( $session_id ) {

	global $wpdb;

	$wpdb->delete($wpdb->prefix . 'yaexam_user_questions', ['session_id' => $session_id], ['%d']);
}

function yaexam_get_doing_user_question( $session_id, $page ) {

	global $wpdb;

	$result = $wpdb->get_row($wpdb->prepare(
		'SELECT * FROM ' . $wpdb->prefix . 'yaexam_user_questions WHERE session_id = %s AND question_order = %d', $session_id, $page), ARRAY_A);

	$result['question_data']     = maybe_unserialize($result['question_data']);
	$result['question_answered'] = maybe_unserialize($result['question_answered']);

	return $result;
}

function yaexam_get_result_user_questions( $result_id ) {

	global $wpdb;

	$results = $wpdb->get_results($wpdb->prepare(
		'SELECT * FROM ' . $wpdb->prefix . 'yaexam_user_questions WHERE result_id = %d', $result_id), ARRAY_A);
	
	if( $results ) {
		foreach( $results as &$result ) {
			$result['question_data'] = maybe_unserialize($result['question_data']);
			$result['question_answered'] = maybe_unserialize($result['question_answered']);
		}
	}
	
	return $results;
}

function yaexam_get_doing_user_result( $session_id ) {

	global $wpdb;

	$session = yaexam_get_user_session($session_id);

	if( !$session ) {
		return false;
	}
	
	$table = $wpdb->prefix . 'yaexam_user_questions';

	$result = $wpdb->get_row(
		"SELECT (SELECT COUNT(id) FROM {$table} WHERE session_id = '{$session_id}' AND question_result = 'right') AS total_corrects, 
				(SELECT COUNT(id) FROM {$table} WHERE session_id = '{$session_id}' AND question_result = 'wrong') AS total_wrongs,
				(SELECT SUM(question_right_score) FROM {$table} WHERE session_id = '{$session_id}' AND question_result = 'right') AS right_score,
				(SELECT SUM(question_wrong_score) FROM {$table} WHERE session_id = '{$session_id}' AND question_result = 'wrong') AS wrong_score,
				(SELECT SUM(question_right_score) FROM {$table} WHERE session_id = '{$session_id}') AS total_score,
				(SELECT COUNT(id) FROM {$table} WHERE session_id = '{$session_id}' AND question_answered IS NULL) AS total_notanswereds", ARRAY_A);

	

	$time_start		=	yaexam_get_date( yaexam_clean_int($session['time_started']) );
	$time_end		=	yaexam_get_date('now');
		
	$result['date_start']	=	$time_start->format('Y-m-d H:i:s');
	$result['date_end']		=	$time_end->format('Y-m-d H:i:s');
	
	$result['user_id']			= $session['user_id'];
	$result['exam_id']			= $session['exam_id'];
	$result['exam_duration'] 	= $session['duration'];
	$result['duration'] 		= yaexam_get_duration( $result['date_start'], $result['date_end'] );

	return $result;
}

function yaexam_update_doing_question( $sid, $page, $data, $formats ) {

	global $wpdb;

	return $wpdb->update($wpdb->prefix . 'yaexam_user_questions', $data, ['session_id' => $sid, 'question_order' => $page], $formats, ['%s', '%d']);
}

function yaexam_update_result_id_user_questions( $sid, $result_id ) {

	global $wpdb;

	return $wpdb->update($wpdb->prefix . 'yaexam_user_questions', ['result_id' => $result_id], ['session_id' => $sid], ['%d'], ['%s']);
}

function yaexam_get_save_laters( $params ) {

	global $wpdb;

	return $wpdb->get_results($wpdb->prepare(
		'SELECT s.*, p.post_title AS exam_name FROM ' . $wpdb->prefix . 'yaexam_save_later s LEFT JOIN ' . $wpdb->prefix . 'posts p ON s.exam_id = p.ID' . ' WHERE user_id = %d', 
			$params['user_id']), ARRAY_A);
}

function yaexam_has_save_later( $exam_id, $user_id ) {

	global $wpdb;

	return $wpdb->get_row($wpdb->prepare(
		'SELECT * FROM ' . $wpdb->prefix . 'yaexam_save_later WHERE exam_id = %d AND user_id = %d', $exam_id, $user_id), ARRAY_A);
}

function yaexam_add_save_later( $params ) {

	global $wpdb;

	$table = $wpdb->prefix . 'yaexam_save_later';

	return $wpdb->insert($table, $params, ['%d', '%d', '%s']);
}

function yaexam_remove_save_later( $id ) {

	global $wpdb;

	$wpdb->delete($wpdb->prefix . 'yaexam_save_later', ['id' => $id], ['%d']);
}

function yaexam_remove_save_later_by_exam_id( $user_id, $exam_id ) {

	global $wpdb;

	$wpdb->delete($wpdb->prefix . 'yaexam_save_later', ['exam_id' => $exam_id, 'user_id' => $user_id], ['%d', '%d']);
}