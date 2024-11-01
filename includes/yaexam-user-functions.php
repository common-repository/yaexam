<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function yaexam_disable_admin_bar( $show_admin_bar ) {
	if ( apply_filters( 'yaexam_disable_admin_bar', get_option( 'yaexam_lock_down_admin', 'yes' ) === 'yes' ) && ! ( current_user_can( 'edit_posts' ) || current_user_can( 'manage_yaexam' ) ) ) {
		$show_admin_bar = false;
	}

	return $show_admin_bar;
}
add_filter( 'show_admin_bar', 'yaexam_disable_admin_bar', 10, 1 );

function yaexam_update_user_meta( $user_id, $data = array() ) {
	
	if(!$user_id || !is_array($data) || !$data) return false;
	
	foreach( $data as $key => $d ) {
				
		update_user_meta( $user_id, $key , $d );
	}	
}

function yaexam_get_user_meta( $user_id, $key = '' ) {
	
	if(!$user_id) return false;
	
	if( !$key ){
		
		$membership_default	=	yaexam_get_membership_default();
	
		$data	=	array(
			'member_id'				=>	0,
			'exams'					=>	array(),
			'did_exams'				=>	array(),
			'paids'					=>	array(),
			'is_payment'			=>	'no',
			'date_paid'				=>	false,
			'member_token'			=>	'',
			'membership_expired'	=>	-1,
			'membership_date_paid'	=>	'0000-00-00 00:00:00',
			'pre_membership'		=>	$membership_default,
			'membership'			=>	$membership_default
		);
		
		foreach( $data as $key => &$value ){
			
			$_value	= get_user_meta( $user_id, $key, true );
			
			if( $_value ) {
				$value	=	$_value;
			}
		}
		
		return $data;
		
	}else{
		
		return get_user_meta( $user_id, $key, true );
		
	}
}

function yaexam_instanceof_wp_user( $user ) {

	return $user instanceof WP_User;
}

function yaexam_update_member_meta( $member_id, $data = array() ) {
	
	if(!$member_id || !is_array($data) || !$data) return false;
	
	foreach( $data as $key => $d ) {
				
		update_post_meta( $member_id, '_' . $key , $d );
	}	
}

function yaexam_get_member_meta( $member_id, $key = '' ) {
	
	if(!$member_id) return false;
	
	$member_data 	=	get_post_meta( $member_id, '_' . $key, true);
	
	return $member_data;
}

function yaexam_update_user_paid( $user_id, $data = array() ) {
	
	if( !$user_id || !$data ) return false;
	
	$user_meta	=	yaexam_get_user_meta( $user_id );
	
	if(isset($data['is_payment'])){
		$user_meta['is_payment']	=	$data['is_payment'];
	}
	
	if(isset($data['paid'])){
		
		$data['paid']['expired']			=	yaexam_date_nextmonth( $data['paid']['date'] );
		
		$user_meta['membership']			=	$data['paid']['membership_id'];
		$user_meta['membership_date_paid']	=	$data['paid']['date'];
		$user_meta['membership_expired']	=	$data['paid']['expired'];
		
		$user_meta['pre_membership']		=	$data['pre_membership'];
		
		array_push( $user_meta['paids'], $data['paid'] );
		
		yaexam_add_member_to_membership( $user_id, $data['paid']['membership_id'] );
	}
	
	yaexam_update_user_meta( $user_id, $user_meta );
}

function yaexam_add_member_to_membership( $user_id, $membership_id ) {
	
	$user_id		=	absInt($user_id);
	$membership_id	=	absInt($membership_id);
	
	$old_membership_id	=	yaexam_get_user_meta( $user_id, 'membership' );
	
	yaexam_update_user_meta( $user_id, array('membership' => $membership_id) );
		
	$old_membership	=	new yaexam_Membership( $old_membership_id );
	$old_membership->remove_member( $user_id );
	
	if( $membership_id ){
		$new_membership	=	new yaexam_Membership( $membership_id );
		$new_membership->add_member( $user_id );
	}
}

function yaexam_add_exam_to_memberships( $exam_id, $memberships = array() ) {
	
	$exam_id			=	absInt($exam_id);
	$all_memberships	=	yaexam_get_memberships();
	$memberships		=	$memberships ? $memberships : array();
	
	if(!$all_memberships) return false;
	
	$all_memberships	=	yaexam_get_values_by_key( $all_memberships, 'ID' );
	
		
	foreach( $all_memberships as $m ){
		
		$membership	=	new yaexam_Membership( $m );
		
		if(in_array($m, $memberships)){
			
			$membership->add_exam( $exam_id );
			
		}else{
			
			$membership->remove_exam( $exam_id );
			
		}
		
	}
	
}

function yaexam_remove_membership( $membership_id = '' ) {
	
	$membership	=	new yaexam_Membership( $membership_id );
	
	$membership->remove();
}

function yaexam_is_membership_expired( $user_id ) {
	
	$membership_id	=	yaexam_get_user_meta( $user_id, 'membership' );
	
	if($membership_id) {
		$membership	=	yaexam_get_memberships( array('ID' => $membership_id), true );
		
		if( $membership->price > 0 ) {
			
			$is_payment	=	yaexam_get_user_meta( $user_id, 'is_payment' );
			
			if( $is_payment == 'no' ) return false;
			
			$expired	=	strtotime( yaexam_get_user_meta( $user_id, 'expired' ) );
			
			if( !$expired ) return true;
			
			if(time() > $expired ){
				
				return true;
			}
			
		}
	}
	
	return false;
}

function yaexam_get_user_paids( $user_id ) {
	
	$user_meta	=	yaexam_get_user_meta( $user_id );
	$data		=	array();
	
	if($user_meta['paids']) {
		foreach( $user_meta['paids'] as &$paid ) {
			
			if($paid['membership_id']){
				$paid['membership']	=	yaexam_get_memberships( array( 'ID' => $paid['membership_id'] ), true);
			}
		}
		
		$data['history']	=	$user_meta['paids'];
	}
	
	if($user_meta['membership']){
		
		$data['membership']		=	yaexam_get_memberships( array( 'ID' => $user_meta['membership'] ), true);
		$data['is_payment']		=	$user_meta['is_payment'];
		$data['date_paid']		=	$user_meta['membership_date_paid'] != '0000-00-00 00:00:00' ? $user_meta['membership_date_paid'] : false;
		$data['expired']		=	$user_meta['membership_expired'] != -1 ? $user_meta['membership_expired'] : false;
		
	}
	
	return $data;
}

function yaexam_update_did_exams( $user_id, $exam_id ) {
	
	if(!$user_id) return false;
	
	$did_exams	=	yaexam_get_user_meta( $user_id, 'did_exams' );
		
	if(!in_array($exam_id, $did_exams)){
		
		array_push( $did_exams, $exam_id );
	}
	
	yaexam_update_user_meta( $user_id, array('did_exams' => $did_exams));
}

function yaexam_get_current_member_id() {
	
	if(!get_current_user_id()) return false;
	
	$member_id	=	yaexam_get_user_meta( get_current_user_id(), 'member_id' );
		
	if( $member_id ) return $member_id;
	
	return false;
}

function yaexam_get_user_exams( $user_id, $page = 1, $perpage = 20 ) {
	global $wpdb;
	
	$exam_ids	=	apply_filters( 'yaexam_before_get_user_exams', yaexam_get_user_meta( $user_id, 'exams' ) );
	
	if( $exam_ids )
	{		
		$exams = get_posts( apply_filters( 'yaexam_my_account_my_exams_query', array(
			'numberposts' 	=>	-1,
			'post_type'   	=>	'exam',
			'include'		=>	$exam_ids
		) ) );
		

		$data = array();

		if($exams){ 
			foreach($exams as &$t){ 

				$t	=	YAEXAM()->exam_factory->get_exam($t->ID); 

				$data[] = array(
					'id' => $t->id,
					'exam_title' => $t->get_title(),
					'exam_link' => $t->get_permalink(),
					'duration' => $t->get_duration(),
					'attempt' => $t->get_attempt(),
				);

			} 
		}
		
		return yaexam_pagination_format( $data, $page, $perpage );
	}
	
	return array('data' => array());
}

function yaexam_get_user_results( $user_id, $page = 1, $perpage	= 10 ) {
	
	global $wpdb;
	
	$result_tbl	=	$wpdb->prefix . 'yaexam_results';
	$exam_tbl	=	$wpdb->prefix . 'posts';
	$page		=	$page ? $page : 1;
	
	$query	=	$wpdb->prepare(
		'SELECT r.id, r.exam_id, r.score, r.total_score, ROUND((r.score * 100)/r.total_score) AS percent, r.duration, r.date_start, r.date_end '.
			'FROM ' . $result_tbl . ' r ' .
			'WHERE r.user_id = %d AND r.date_start IN(SELECT max(dr.date_start) FROM ' . $result_tbl . ' dr WHERE dr.user_id = %d GROUP BY dr.exam_id) '.
			'GROUP BY r.exam_id ORDER BY r.date_start DESC'
		, $user_id, $user_id);
	
	$results	=	$wpdb->get_results($query, ARRAY_A);
		
	return yaexam_pagination_format( $results, $page, $perpage );
}

function yaexam_get_new_user( $data, $params = array() ) {
	
	$username	=	! empty( $data[ 'username' ] ) ? sanitize_user( $data[ 'username' ], 50 ) : '';
	$email      =	! empty( $data[ 'email' ] ) ? sanitize_email( $data[ 'email' ] ) : '';
	$password   =	! empty( $data[ 'password' ] ) ? $data[ 'password' ] : '';
	
	$user         = new stdClass();
	
	if ( $email ) {
		
		if ( ! is_email( $email ) ) {
			yaexam_add_message( __( 'Please provide a valid email address.', 'yaexam' ), 'error' );
		} elseif ( email_exists( $email ) ) {
			yaexam_add_message( __( 'This email address is already registered.', 'yaexam' ), 'error' );
		}
		
		$user->user_email = $email;
	} else {
		
		yaexam_add_message( __( 'This email is empty.', 'yaexam' ), 'error' );
	}

	if( isset($params['is_email_for_username']) && $params['is_email_for_username'] == 'yes') {

		$username = $email;
	}
	
	if( $username ) {
		
		if( username_exists( $username ) ) {
			yaexam_add_message( __( 'This username is already registered.', 'yaexam' ), 'error' );
		}
		
		$user->user_login	=	$username;
	}else{
		
		yaexam_add_message( __( 'This username is empty.', 'yaexam' ), 'error' );
	}
	
	$user->user_pass 	=	$password;
	$user->role			=	'subscriber';
	
	return $user;
}

function yaexam_new_guest( $is_login = true ) {
	
	$id					=	uniqid();
	$username			=	'guest_' . $id;
	
	$user         		=	new stdClass();
	
	$user->user_login	=	$username;
	$user->user_pass 	=	'';
	$user->role			=	'yaexam_member';
	
	$user_id = wp_insert_user( $user ) ;
	
	update_user_meta($user_id, 'is_guest', 1);
	
	if( $is_login ){
		wp_set_current_user($user_id);
		wp_set_auth_cookie($user_id);
	}
	
	return $user_id;
}

function yaexam_new_member( $user_id, $args = array() ) {
	
	$user_id	=	absInt($user_id);
	
	if(!$user_id) return false;
	
	$membership_default	=	yaexam_get_membership_default();
	
	$args	=	wp_parse_args( $args, array(
		'pre_membership'	=>	$membership_default,
		'membership'		=>	$membership_default,
		'is_payment'		=>	'no'
	));
	
	$is_payment	=	$args['is_payment'] == 'yes' ? 'yes' : 'no';
	
	$user = new WP_User( $user_id );
	
	if(!empty( $user->roles ) && is_array( $user->roles ) && in_array('yaexam_member', $user->roles)) {
		
		$member_id	=	wp_insert_post(array(
			'post_type'		=>	'member',
			'post_title'	=>	$user->nickname,
			'post_status'	=>	'publish',
			'meta_input'	=>	array(
				'_user_id'	=>	$user_id
			)
		));
		
		yaexam_update_user_meta( $user_id, array(
			'pre_membership'	=> absInt($args['pre_membership']), 
			'membership' 		=> absInt($args['membership']), 
			'is_payment' 		=> 'no', 
			'member_id' 		=> $member_id
		) );
		
		return $member_id;
	}
	
	return false;
}

function yaexam_update_new_membership( $user_id ) {
	
	$pre_membership	=	yaexam_get_user_meta( $user_id, 'pre_membership' );
	
	$data			=	array();
	
	$data['membership']		=	$pre_membership;
	$data['pre_membership']	=	yaexam_get_membership_default();
	$data['member_token']	=	'';
	
	yaexam_update_user_meta( $user_id, $data );
}

function yaexam_get_users( $search = '', $params	=	array() ) {
	
	$params	=	wp_parse_args($params, array('search_columns' => array( 'user_login', 'user_email', 'user_nicename' ) ) );
	
	if($search){
		$params['search']	=	'*'.esc_attr( $search ).'*';
	}
	
	$user_query = new WP_User_Query( $params );
	
	$results	=	array();
	
	if ( ! empty( $user_query->results ) ) {
		foreach($user_query->results as $u){
			
			array_push($results, array('id' => $u->ID, 'name' => ($u->display_name . ' - ' . $u->user_email)));
		}
	}
	
	return $results;
}

function yaexam_get_memberships( $args	=	array(), $single = false ) {
	
	$args	=	wp_parse_args( $args, array('numberposts' => -1, 'post_type' =>	'membership') );
	
	if(isset($args['ID'])){
		$args['include']	=	array(absInt($args['ID']));
	}
	
	$memberships = get_posts( apply_filters( 'yaexam_admin_get_memberships_query', $args ) );
	
	if( $memberships ) {
		foreach( $memberships as $membership ) {
			
			$price	=	yaexam_get_post_meta( $membership->ID, 'price' );
			
			if(isset($price)){ $membership->price	=	absInt( $price ); }
			
			$membership->link	=	yaexam_get_page_permalink('archive_exam') . '?mid=' . $membership->ID;
		}
		
		return $single ? $memberships[0] : $memberships;
	}
	
	return false;
}

function yaexam_dropdown_memberships( $name, $selected = 0, $attrs = '', $echo = true ) {
	
	$memberships	=	yaexam_get_memberships();
	
	if(!$memberships) return false;
	
	$options		=	'<option value="0"' . selected(0, $selected, false) . '>' . __('Select All', 'yaexam') . '</option>';
	
	foreach($memberships as $m) {
		$options	.=	sprintf('<option value="%d" ' . selected($m->ID, $selected, false) . '>%s</option>', $m->ID, $m->post_title);
	}
	
	$output	= sprintf('<select name="%s" %s>%s</select>', $name, $attrs, $options);
	
	if( $echo ) {
		
		echo $output;
		
	}else{
		
		return $output;
	}
}

function yaexam_get_membership_default() {
	
	return get_option('yaexam_membership_default', 0);
}

function yaexam_set_membership_default( $id ) {
	
	update_option('yaexam_membership_default', absInt($id));
}

function yaexam_get_user_by_member( $member_id ) {
	
	$user_id	=	yaexam_get_member_meta( $member_id, 'user_id' );
	
	if( $user_id ) {
		
		$user	=	new WP_User( $user_id );
		$user_meta	=	yaexam_get_user_meta( $user_id );
		
		if( $user ){
			return array( 'user' => $user, 'meta' => $user_meta );
		}
		
		return false;
	}
	
	return false;
}

function yaexam_is_alive_session( $session_key = 0 ) {
	
	return true;
	global $wpdb;
	
	if(!$session_key) return false;
	
	$session_key	=	absint($session_key);
	
	$results	=	$wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'yaexam_sessions WHERE session_key = ' . $session_key, ARRAY_A );
	
	if( !$results ) return false;
	
	if(count($results) > 1) {
		
		for( $i = 0; $i < count($results); $i++ ) {
			yaexam_remove_session_doing( $results[$i]['session_id'] );
		}
		
		return false;
	}
	
	return true;
}

function yaexam_remove_session_doing( $session_id = 0 ) {

	global $wpdb;
	
	if(!$session_id) return false;
	
	$result	= $wpdb->delete( $wpdb->prefix . 'yaexam_sessions', array('session_id' => $session_id), array('%d') );
	
	$wpdb->flush();
	
	return $result;
}

function yaexam_get_user_groups() {
	
	return get_posts(array(
			'post_type' 	 => 'usergroup',
			'posts_per_page' => -1
		));
}

function yaexam_can_doing_form() {

	$is_doing_form = is_doing_form();

	if( $is_doing_form ){

		$session    = 	new yaexam_Test_Session();

		$result_id = $session->get('result_id');
		$exam_id   = $session->get('exam_id');
		
		$is_redirect = true;

		if( $result_id ) {

			

			if( $exam_id == get_the_ID() ) {

				$is_redirect = false;
			}
		}
		
		if( $is_redirect ) {
			
			wp_redirect( get_permalink( $exam_id ) );
			exit;
		}

		return true;
	}

	return false;
}

function yaexam_get_user_score( $user_id ) {

	$score_1 = get_user_meta( $user_id, '_score', true );

	return $score_1 ? absint( $score_1 ) : 0;
}

function yaexam_update_user_score( $score_2, $user_id ) {

	$score_1 = yaexam_get_user_score( $user_id );

	$score_2 = absint($score_2) + $score_1;

	update_user_meta( $user_id, '_score', $score_2 );
}

function yaexam_new_user_score( $score_2, $user_id ) {

	update_user_meta( $user_id, '_score', $score_2 );
}

function yaexam_reset_user_score( $user_id ) {

	update_user_meta( $user_id, '_score', 0 );
}