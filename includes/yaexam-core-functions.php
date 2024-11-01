<?php 

if ( ! defined( 'ABSPATH' ) ) { exit; }

include( 'yaexam-formatting-functions.php' );
include( 'yaexam-conditional-functions.php' );
include( 'yaexam-page-functions.php' );
include( 'yaexam-user-functions.php' );

function yaexam_init_class() {

	return new stdClass();
}

function yaexam_update_or_new_array_meta( $post_id, $data, $meta_key, $id = false, $key = 'id' ) {

	if( !$post_id ) {

		return false;
	}

	$stored_data = get_post_meta( $post_id, $meta_key, true );

	if( $id && $stored_data && is_array($stored_data) ) {

		foreach( $stored_data as &$item ) {

			if( $item[$key] == $id ) {

				$item = $data;
			}
		}

	}elseif( !$id && $stored_data && is_array($stored_data) ){

		array_push( $stored_data, $data );

	}else{

		$stored_data = array( $data );
	}

	update_post_meta( $post_id, $meta_key, $stored_data );

	return $stored_data;
}

function yaexam_remove_array_meta( $post_id, $remove_ids, $meta_key, $key = 'id' ) {

	if( !$post_id || !$remove_ids ) {

		return false;
	}

	if( !is_array( $remove_ids ) ) {

		$remove_ids = array($remove_ids);
	}

	$stored_data = get_post_meta( $post_id, $meta_key, true );

	if( $stored_data && is_array($stored_data) ) {

		foreach( $stored_data as $index => &$question ) {

			if( in_array($question[$key], $remove_ids) ) {

				unset( $stored_data[$index] );
			}
		}
	}

	update_post_meta( $post_id, $meta_key, $stored_data );

	return $stored_data;
}

function yaexam_update_element_to_array_post_meta( $post_id, $value, $meta_key ) {

	if( !$post_id ) {

		return false;
	}

	$stored_data = get_post_meta( $post_id, $meta_key, true );

	if( $stored_data && is_array($stored_data) ) {
		
		array_push( $stored_data, $value );
		
		if( !is_array( $value ) ){
			
			$stored_data = array_unique($stored_data);
			
		}
		
	}else{

		$stored_data = array( $value );
	}

	update_post_meta( $post_id, $meta_key, $stored_data );

	return $stored_data;
}

function yaexam_update_elements_to_array_post_meta( $post_id, $values, $meta_key ) {

	if( !$post_id || !is_array($values) ) {

		return false;
	}
	
	$stored_data = get_post_meta( $post_id, $meta_key, true );

	if( $stored_data ) {

		$stored_data = array_unique(array_merge( $stored_data, $values ));

	}else{

		$stored_data 	= array_unique($values);
	}

	update_post_meta( $post_id, $meta_key, $stored_data );

	return $stored_data;
}

function yaexam_remove_elements_from_array_post_meta( $post_id, $values, $meta_key ) {

	if( !$post_id ) {

		return false;
	}

	if( !is_array( $values ) ) {

		$values = array( $values );
	}

	$stored_data = get_post_meta( $post_id, $meta_key, true );

	if( $stored_data ) {

		$stored_data = array_diff( $stored_data, $values );

	}else{

		$stored_data 	= array();
	}

	update_post_meta( $post_id, $meta_key, $stored_data );

	return $stored_data;
}

function yaexam_checking_return( $instance, $key, $default = false ) {

	if(isset($instance) && isset($instance[$key])){
		return $instance[$key];
	}else{
		return $default;
	}
}

function yaexam_update_post_meta ( $post_id, $data = array() ) {
	
	if(!$post_id || !is_array($data) || !$data) return false;
	
	foreach( $data as $key => $d ) {
				
		update_post_meta( $post_id, '_' . $key , $d );
	}

	return true;
}

function yaexam_get_post_meta( $post_id, $key = '', $default = false ) {
	
	if(!$post_id) return $default;
	
	$data 	=	get_post_meta( $post_id, '_' . $key, true);
	
	if( !isset($data) ) return $default;
	
	return $data;
}

function yaexam_update_post_meta_array( $post_id, $key, $value, $is_unique = true ) {
	
	if( !isset($post_id) || !isset($key) || !isset($value) ) return false;
	
	$data	=	yaexam_get_post_meta( $post_id, $key );
	
	$data	=	is_array( $data ) ? $data : array();
	
	array_push( $data, $value );
	
	if( $is_unique ) $data	=	array_unique( $data );
	
	
	yaexam_update_post_meta( $post_id, array( $key => $data ) );
}

function yaexam_remove_post_meta_array( $post_id, $key, $value ) {
	
	if( !isset($post_id) || !isset($key) || !isset($value) ) return false;
	
	$data	=	yaexam_get_post_meta( $post_id, $key );
	
	yaexam_update_post_meta( $post_id, array( $key => yaexam_array_remove_by_value( $data, $value ) ) );
}

function yaexam_array_remove_by_value( $data = array(), $value = '', $is_reset_index = true ) {
	
	if( !is_array($data) || !$data || !isset($value) ) return array();
	
	if( !in_array( $value, $data ) ) return $data;
	
	foreach( $data as $index => $v ){
		
		if( $v == $value ){
			
			unset($data[$index]);
		}
		
	}
	
	if( $is_reset_index ) {
		
		return array_values( $data );
		
	}else{
		
		return $data;
	}
}

function yaexam_get_values_by_key( $data = array(), $key ) {

	if(!$data || !$key) return false;
	
	$results	=	array();
	
	foreach($data as $d){
		
		if(is_object($d))
			$d = get_object_vars($d);
		
		if(isset($d) && isset($d[$key]))
		{
			array_push($results, $d[$key]);
		}
	}
	
	return $results;
}

function yaexam_get_data_by_key( $data = array(), $key, $value ) {

	if( !$data ) return false;

	foreach( $data as $d ) {

		if( isset($d[$key]) && ($d[$key] == $value) ) {

			return $d;
		}
	}

	return false;
}

function yaexam_setcookie( $name, $value, $expire = 0, $secure = false ) {
	if ( ! headers_sent() ) {
		setcookie( $name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure );
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		headers_sent( $file, $line );
		trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE );
	}
}

function yaexam_crypto_rand_secure($min, $max)
{
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd >= $range);
    return $min + $rnd;
}

function yaexam_get_token($length)
{
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789-";
    $max = strlen($codeAlphabet) - 1;
    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[yaexam_crypto_rand_secure(0, $max)];
    }
    return $token;
}

function yaexam_get_setting( $name ) {
	
	return get_option( 'yaexam_' . $name );
}

function yaexam_get_timezone_offset() {
	
	return get_option('gmt_offset');
}

function yaexam_get_timezone(){
	
	if ( $timezone = get_option( 'timezone_string' ) )
		return $timezone;

	if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) )
		return 'UTC';
	
	// adjust UTC offset from hours to seconds
	$utc_offset *= 3600;

	// attempt to guess the timezone string from the UTC offset
	if ( $timezone = timezone_name_from_abbr( '', $utc_offset, 0 ) ) {
	    return $timezone;
	}

	// last try, guess timezone string manually
	$is_dst = date( 'I' );

	foreach ( timezone_abbreviations_list() as $abbr ) {
	    foreach ( $abbr as $city ) {
	        if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset )
	            return $city['timezone_id'];
	    }
	}

	// fallback to UTC
	return 'UTC';
}

function yaexam_time( $timezone = '' ) {
	if( !$timezone ) {
		$timezone	=	yaexam_get_timezone();
	}
	
	date_default_timezone_set($timezone);
	
	return time();
}

function yaexam_get_date( $value = '', $format = '', $timezone = '' )
{
	if( !$timezone ) {
		$timezone	=	yaexam_get_timezone();
	}
	
	date_default_timezone_set($timezone);
	
	if( !$format ) {
		$format	=	'Y-m-d H:i:s';
	}
	
	if( !$value || $value == 'now' ) {
		
		return new DateTime( 'now', new DateTimeZone( $timezone ) );
	}
	
	if( is_string($value) ){
		$value = strtotime( $value );
		
		return new DateTime( date( $format, $value ), new DateTimeZone( $timezone ) );
	}else{
		
		return new DateTime( date($format, $value), new DateTimeZone( $timezone ) );
	}
}

function yaexam_get_date_formated( $value, $format = '', $timezone = '' ) {
	
	$date	=	yaexam_get_date( $value, $format, $timezone );
	
	if(!$format) {
		$format	=	get_option('date_format') . ' ' . get_option('time_format');
	}
	
	return date_i18n( $format, strtotime($date->format( $format )) );
}

function yaexam_get_exam_types() {

	return apply_filters('yaexam_exam_types', [
		['name' => 'normal', 'label' => 'Normal Exam'],
	]);
}

function yaexam_get_duration( $date_start, $date_end, $format = 'Y-m-d H:i:s', $timezone = '' )
{
	
	$date_s 	= 	yaexam_get_date( $date_start, $format, $timezone );
	$date_e 	= 	yaexam_get_date( $date_end, $format, $timezone );
	
	$interval	=	$date_e->diff($date_s);
	
	return $interval->format('%H:%I:%S');
}

function yaexam_get_seconds( $date_start, $date_end, $format = 'Y-m-d H:i:s', $timezone = '' )
{
	
	$date_s 	= 	yaexam_get_date( $date_start, $format, $timezone );
	$date_e 	= 	yaexam_get_date( $date_end, $format, $timezone );
	
	$interval	=	$date_e->diff($date_s);
	
	return $interval->format('%S');
}

function yaexam_is_expired( $time_start, $time_end, $duration ) {
	
	$current_duration	=	$time_end - $time_start;
	$duration			=	$duration * 60;

	if($current_duration > $duration){

		return true;
	}

	return false;
}
function yaexam_check_recaptcha( $params = array() ) {
	
	$captcha	=	is_yaexam_enable_captcha();
	
	if(!$captcha){
		return true;
	}
	
	$args	=	array(
		'secret'	=>	$captcha['secret'],
		'response'	=>	$params['response'],
		'remoteip'	=>	$params['remoteip']
	);
	
	$results	=	yaexam_http_post('https://www.google.com/recaptcha/api/siteverify', $args);
	
	$results	=	$results ? json_decode( $results ) : false;
	
	if($results) {
			
		return $results->success;
		
	}else{
		
		return false;
	}
}

function yaexam_get_start_exam_url( $exam_id = 0 )
{
	global $exam;
	
	if(!$exam_id) {
		$exam_id = get_the_ID();
	}
	
	return get_permalink( $exam_id );
}

function yaexam_get_doing_exam_url()
{
	global $exam;

	return add_query_arg( 'yaexam-doing', 1, get_permalink($exam->ID) );
	
}

function yaexam_get_ranking_exam_url()
{
	global $exam;

	return add_query_arg( 'ranking', 1, get_permalink($exam->ID) );
}

function yaexam_get_result_exam_url( $result_id = false, $exam_id = false )
{
	global $exam;

	if(isset($exam) && (!isset($exam_id) || !$exam_id))
	{
		$exam_id = $exam->id;
	}
	
	if($result_id){

		return add_query_arg( 'result', $result_id, get_permalink($exam_id) );

	}else{

		if($exam){
			return get_permalink($exam_id);
		}else{

			return '';
		}
	}
	
}

function yaexam_view_certificate_exam_url( $exam_id, $result_id = false )
{	
	if( !$result_id || !$exam_id ) return false;

	return add_query_arg( 'view-certificate', $result_id, get_permalink($exam_id) );
}

function yaexam_is_owner_exam( $exam_id, $user_id = 0 ) {
	
	if(!$user_id){ $user_id	=	get_current_user_id(); }
	
	$exam = YAEXAM()->exam_factory->get_exam($exam_id);
	
	if($exam && ($exam->get_author_id() == $user_id)) {
		return true;
	}
	
	return false;
}

function yaexam_is_owner_question( $question_id, $user_id = 0 ) {
	
	if(!$user_id){ $user_id	=	get_current_user_id(); }
	
	$question = new EM_Question($question_id);
	
	if($question && ($question->get_author_id() == $user_id)) {
		return true;
	}
	
	return false;
}

function yaexam_is_owner_result( $result_id, $user_id = 0 ) {
	
	if(!$user_id){ $user_id	=	get_current_user_id(); }
	
	$result	=	EM_Test::get_result( $result_id );
	
	if($result){
		
		return $result['user_id'] == $user_id ? $result : false;
	}
	
	return false;
}

function yaexam_is_assigned_to_exam( $exam_id, $user_id ) {
	
	if(!$user_id){ $user_id	=	get_current_user_id(); }
	
	$user_exams	=	yaexam_get_user_meta( $user_id, 'exams' );
	
	if( in_array($exam_id, $user_exams) ){
		
		return true;
	}
	
	return false;
}

function yaexam_can_edit_exam( $exam_id, $user_id = 0 ) {
		
	if(is_owner_exam( $exam_id, $user_id )){
		
		return true;
	}
	
	return false;
}

function yaexam_can_edit_question( $question_id, $user_id = 0 ) {
	
	if(!$user_id){ $user_id	=	get_current_user_id(); }
	
	if(is_owner_question( $question_id, $user_id )){
		
		return true;
	}
	
	return false;
}

function yaexam_can_view_result( $result_id, $exam_id, $user_id = 0 ) {
	
	if(!$user_id){ $user_id	=	get_current_user_id(); }
	
	$user = wp_get_current_user();

	if( is_owner_result( $result_id, $user_id ) || is_owner_exam( $exam_id, $user_id ) || in_array( 'administrator', (array) $user->roles ) ){
		
		return true;
	}
	
	return false;
}

function yaexam_can_edit() {
	
	if(!is_user_logged_in()) return false;
	
	if(current_user_can('edit_exam')){
		return true;
	}
}

function yaexam_can_do_exam( $exam_id = 0, $user_id = 0 ){
	
	global $exam;
		
	$publish_for	=	yaexam_get_post_meta( $exam_id, 'publish_for' );

	$can_play = false;

	if(!is_user_logged_in()) {
		return false;
	}
	
	if(yaexam_is_admin()){
		return true;
	}
	
	// is private
	if($publish_for == 1){
		
		return current_user_can('edit_posts');

	// is user
	}elseif($publish_for == 2){

		$exam	=	YAEXAM()->exam_factory->get_exam($exam_id);

		$attempt		=	yaexam_get_post_meta( $exam_id, 'attempt' );

		$current_user	=	wp_get_current_user();
		$attempts		=	absInt($attempt);
		
		if($attempts > 0){
			
			$results	=	yaexam_exam_get_user_attempt($exam_id, $user_id);
			
			if(is_array($results) && $results['user_attempts'] >= $attempts){

				$can_play = false;

			}else{

				$can_play = true;
			}

		}else{

			$can_play = true;
		}
	
	// is guest and user
	}else{
		
		$can_play = true;
	}
	
	return apply_filters( 'yaexam_can_do_exam', $can_play, $exam_id );
}

function yaexam_is_guest() {
	
	if( !is_user_logged_in() && get_option('yaexam_is_play_exam_as_guest') == 'yes' ) return true;
	
	return false;
}

function yaexam_can_do_exam_as_guest( $exam_id = 0 ) {
	global $exam;
	
	if( is_user_logged_in() || get_option('yaexam_is_play_exam_as_guest') == 'no' ) return false;
	
	if( $exam_id ) {

		$exam	=	new EM_Exam($exam_id);
	}
	
	$publish	=	$exam->get_publish();
	
	// for every user
	if($publish == 0){
		
		return true;
	}
	
	return false;
}

function yaexam_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$tip = yaexam_sanitize_tooltip( $tip );
	} else {
		$tip = esc_attr( $tip );
	}

	return '<span class="yaexam-help-tip" data-tip="' . $tip . '"></span>';
}

function yaexam_image_tag( $attachment_id, $size = 'thumbnail', $attr = true ) {
	
	if( !$attachment_id ) return false;
	
	$background_attr	=	'';
	
	$background_src		=	wp_get_attachment_image_src( $attachment_id, $size );
	
	if( !$background_src ) return false;
	
	if( $attr ) {
		$background_attr	=	'width="' . $background_src[1] . '" height="' . $background_src[2] . '"';
	}
	
	return sprintf('<img src="%s" %s/>', $background_src[0], $background_attr);
}

function yaexam_is_debug() {
	if(get_option('yaexam_is_debug') == 'yes'){
		return true;
	}else{
		return false;
	}
}

function yaexam_get_all_categories()
{
	global $wpdb;

	return $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'yaexam_question_categories', ARRAY_A );
}

function yaexam_get_categories( $cat_ids )
{
	global $wpdb;

	if(!$cat_ids) { return false; }

	$categories = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'yaexam_question_categories WHERE id IN (' . implode(', ', $cat_ids) . ')', ARRAY_A);

	$new_categories = array();

	if( $categories ) {
		
		foreach( $categories as $cat )
		{
			$new_categories[$cat['id']] = $cat['name'];
		}
	}

	return $new_categories;
}


function yaexam_get_params_question( $question_id, $all_params = array() )
{
		
	if( isset($question_id) && isset($all_params) && $all_params )
	{ 
		foreach( $all_params as $params ){
			if( $params['id'] == $question_id )
			{
				return $params;
			}
		}
	}

	return array();
}

function yaexam_get_log_file_path( $handle ) {
	return trailingslashit( YAEXAM_LOG_DIR ) . $handle . '-' . sanitize_file_name( wp_hash( $handle ) ) . '.log';
}