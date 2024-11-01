<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function is_yaexam() {
	return apply_filters( 'is_yaexam', ( is_exam_taxonomy() || is_exam() ) ? true : false );
}

function yaexam_is_myaccount() {
	return is_page( yaexam_get_page_id( 'myaccount' ) ) || defined( 'YAEXAM_MYACCOUNT' );
}


function is_exam_taxonomy() {
	return is_tax( get_object_taxonomies( 'exam' ) );
}



function is_exam_category( $term = '' ) {
	return is_tax( 'exam_cat', $term );
}



function is_exam_tag( $term = '' ) {
	return is_tax( 'exam_tag', $term );
}



function is_exam() {
	return is_singular( array( 'exam' ) );
}

function is_start() {
	global $wp_the_query;
	
	return isset($wp_the_query->query_vars['start']);
}


function yaexam_is_doing() {
	global $wp_the_query;
	
	return isset($wp_the_query->query_vars['em-doing']);
}


function is_doing_form() {
	global $wp_the_query;
	
	return isset($wp_the_query->query_vars['doing-form']);
}


function yaexam_is_result() {
	global $wp_the_query;
	
	return isset($wp_the_query->query_vars['em-result']);
}


function is_certificate() {
	global $wp_the_query;
	
	return isset($wp_the_query->query_vars['view-certificate']);
}


function is_ranking() {
	global $wp_the_query;
	
	return isset($wp_the_query->query_vars['ranking']);
}


function is_play_exam_as_guest() {
	
	$result = get_option('yaexam_is_play_exam_as_guest');

	if( $result == 'yes' ) {

		return true;
		
	}else{

		return false;
	}
}


function is_user_fillform_setting() {
	
	$result = get_option('yaexam_is_user_fillform_setting');

	if( $result == 'yes' ) {

		return true;
		
	}else{

		return false;
	}
}


function is_user_ranking_view_result_setting() {
	
	$result = get_option('yaexam_is_user_ranking_view_result_setting');

	if( $result == 'yes' ) {

		return true;
		
	}else{

		return false;
	}
}


function is_email_result( $exam_id ) {
	
	if(!$exam_id || yaexam_is_user_guest()) return false;
	
	$settings	=	yaexam_exam_get_settings($exam_id);
	
	return $settings['is_email_result'];
}


function is_show_badge_memberships() {
	
	$is_show	=	yaexam_get_setting('is_show_badge_memberships');
	
	return $is_show == 'yes' ? true : false;
}


function is_yaexam_container_class() {
	$is_left_sidebar	=	is_active_sidebar( 'yaexam-left-sidebar' );
	$is_right_sidebar	=	is_active_sidebar( 'yaexam-right-sidebar' );
	
	$container			=	'aws-sm-12';
	
	if( $is_left_sidebar && !$is_right_sidebar ) {
		$container	=	'aws-sm-9 last';
	}else if( !$is_left_sidebar && $is_right_sidebar ) {
		$container	=	'aws-sm-9';
	}else if( $is_left_sidebar && $is_right_sidebar ) {
		$container	=	'aws-sm-6';
	}
	
	return $container;
}


function is_yaexam_enable_captcha() {
	$is_enabled		=	get_option('yaexam_is_captcha_on_exam');
	$captcha_key	=	get_option('yaexam_is_captcha_key');
	$captcha_secret	=	get_option('yaexam_is_captcha_secret');
	
	if( $is_enabled == 'yes' && $captcha_key && $captcha_secret ) {
		
		return array( 'key' => $captcha_key, 'secret' => $captcha_secret );
	}
	
	return false;
}

function yaexam_is_user_guest(){
	$user_id	=	get_current_user_id();
	
	$is_guest = get_user_meta( $user_id, 'is_guest', true );
	
	return $is_guest == 1 ? true : false;
}

function yaexam_is_admin() {

	$user = wp_get_current_user();
	$allowed_roles = array('administrator', 'author');

	if( array_intersect($allowed_roles, $user->roles ) ) {
		return true;
	}else{
		return false;
	}
}

function yaexam_is_show_header() {
	return true;
}
