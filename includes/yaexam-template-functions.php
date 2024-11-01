<?php

use YaExam\YAEXAM_Exam;

function yaexam_get_template_part( $slug, $name = '' ) {
	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/woocommerce/slug-name.php
	if ( $name ) {
		$template = locate_template( array( "{$slug}-{$name}.php", YAEXAM()->template_path() . "{$slug}-{$name}.php" ) );
	}
	
	// Get default slug-name.php
	if ( ! $template && $name && file_exists( YAEXAM()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
		$template = YAEXAM()->plugin_path() . "/templates/{$slug}-{$name}.php";
	}
	
	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/woocommerce/slug.php
	if ( ! $template ) {
		$template = locate_template( array( "{$slug}.php", YAEXAM()->template_path() . "{$slug}.php" ) );
	}

	// Allow 3rd party plugins to filter template file from their plugin.
	$template = apply_filters( 'yaexam_get_template_part', $template, $slug, $name );

	if ( $template ) {
		load_template( $template, false );
	}
}

function yaexam_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}

	$located = yaexam_locate_template( $template_name, $template_path, $default_path );

	$located = apply_filters( 'yaexam_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action( 'yaexam_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'yaexam_after_template_part', $template_name, $template_path, $located, $args );
}

function yaexam_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = YAEXAM()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = YAEXAM()->plugin_path() . '/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);
	
	// Get default template/
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'yaexam_locate_template', $template, $template_name, $template_path );
}

function yaexam_setup_exam_data( $post ) {
	unset( $GLOBALS['exam'] );

	if ( is_int( $post ) )
		$post = get_post( $post );
	
	if ( empty( $post->post_type ) || ! in_array( $post->post_type, array( 'exam' ) ) )
		return;

	$GLOBALS['exam'] = yaexam_get_exam( $post );

	return $GLOBALS['exam'];
}
add_action( 'the_post', 'yaexam_setup_exam_data' );

function yaexam_body_class( $classes ) {
	$classes = (array) $classes;
	
	if ( is_yaexam() ) {
		$classes[] = 'yaexam';
		$classes[] = 'yaexam-page';
	}
	
	return array_unique( $classes );
}
 
	
function yaexam_container_class( $class = '' ) {
	echo 'class="' . join( ' ', yaexam_get_container_class( $class ) ) . '"';
}

function yaexam_get_container_class( $class = '' ) {
	
	$classes	=	[];
	
	if( !$class ) {
		if( is_array( $class ) ){
			
			$classes	=	array_merge($classes, $class);
		}else{
			
			array_push( $classes, $class );
			$classes	=	array_unique( $classes );
		}
	}
	
	return apply_filters('yaexam_container_class', $classes, $class );
}

function yaexam_container_column_class( $classes = array(), $class = '' ){
	
	$is_left_sidebar	=	is_active_sidebar( 'yaexam-left-sidebar' );
	$is_right_sidebar	=	is_active_sidebar( 'yaexam-right-sidebar' );
	
	$container_class	=	'em-col-sm-12';
	
	if( $is_left_sidebar && !$is_right_sidebar ) {
		$container_class	=	'em-col-md-9';
	}else if( !$is_left_sidebar && $is_right_sidebar ) {
		$container_class	=	'em-col-md-9';
	}else if( $is_left_sidebar && $is_right_sidebar ) {
		$container_class	=	'em-col-sm-6';
	}	
	
	array_push( $classes, $container_class );
	
	return array_unique( $classes );
}
add_filter('yaexam_container_class', 'yaexam_container_column_class', 10, 2);

if ( ! function_exists( 'yaexam_get_left_sidebar' ) ) {
	
	function yaexam_get_left_sidebar() {
		yaexam_get_template( 'global/left-sidebar.php' );
	}
}

if ( ! function_exists( 'yaexam_get_right_sidebar' ) ) {
	
	function yaexam_get_right_sidebar() {
		yaexam_get_template( 'global/right-sidebar.php' );
	}
}

if ( ! function_exists( 'yaexam_output_content_wrapper_start' ) ) {

	function yaexam_output_content_wrapper_start() {
		
		yaexam_get_template( 'global/wrapper-start.php' );
	}
}

if ( ! function_exists( 'yaexam_output_content_wrapper_end' ) ) {

	function yaexam_output_content_wrapper_end() {
		yaexam_get_template( 'global/wrapper-end.php' );
	}
}

if ( ! function_exists( 'yaexam_exam_loop_start' ) ) {
	
	function yaexam_exam_loop_start( $echo = true ) {
		ob_start();
		yaexam_get_template( 'loop/loop-start.php' );
		if ( $echo )
			echo ob_get_clean();
		else
			return ob_get_clean();
	}
}

if ( ! function_exists( 'yaexam_exam_loop_end' ) ) {
	
	function yaexam_exam_loop_end( $echo = true ) {
		ob_start();
		yaexam_get_template( 'loop/loop-end.php' );

		if ( $echo )
			echo ob_get_clean();
		else
			return ob_get_clean();
	}
}

if( ! function_exists( 'yaexam_template_filter_wrap_before_nav') ) {

	function yaexam_template_filter_wrap_before_nav() {

		echo '<nav class="em-navbar"><ul>';
	}
}

if( ! function_exists( 'yaexam_template_filter_wrap_after_nav') ) {

	function yaexam_template_filter_wrap_after_nav() {

		echo '</ul></nav>';
	}
}

if ( ! function_exists( 'yaexam_catalog_ordering' ) ) {
	
	function yaexam_catalog_ordering() {
		global $wp_query;

		if ( 1 === $wp_query->found_posts ) {
			return;
		}

		$orderby                 = isset( $_GET['orderby'] ) ? yaexam_clean( $_GET['orderby'] ) : apply_filters( 'yaexam_default_catalog_orderby', get_option( 'yaexam_default_exam_orderby' ) );
		
		$show_default_orderby    = 'menu_order' === apply_filters( 'yaexam_default_catalog_orderby', get_option( 'yaexam_default_exam_orderby' ) );
		$catalog_orderby_options = apply_filters( 'yaexam_catalog_orderby', array(
			'menu_order' 	=> __( 'Default sorting', 'yaexam' ),
			'date'			=> __( 'Sort by newness', 'yaexam' ),
			'duration-asc'  => __( 'Sort by duration: low to high', 'yaexam' ),
			'duration-desc' => __( 'Sort by duration: high to low', 'yaexam' ),
		) );

		if ( ! $show_default_orderby ) {
			unset( $catalog_orderby_options['menu_order'] );
		}

		if ( 'no' === get_option( 'yaexam_enable_review_rating' ) ) {
			unset( $catalog_orderby_options['rating'] );
		}

		yaexam_get_template( 'loop/orderby.php', array( 'catalog_orderby_options' => $catalog_orderby_options, 'orderby' => $orderby, 'show_default_orderby' => $show_default_orderby ) );
	}
}

if ( ! function_exists( 'yaexam_pagination' ) ) {
	
	function yaexam_pagination() {
		yaexam_get_template( 'loop/pagination.php' );
	}
}

if (  ! function_exists( 'yaexam_template_loop_exam_wrapper_desc_start' ) ) {
	
	function yaexam_template_loop_exam_wrapper_desc_start() {
		echo '<div class="em-item-desc">';
	}
}

if (  ! function_exists( 'yaexam_template_loop_exam_wrapper_desc_end' ) ) {

	function yaexam_template_loop_exam_wrapper_desc_end() {
		echo '</div></div>';
	}
}

if (  ! function_exists( 'yaexam_template_loop_exam_category' ) ) {
	
	function yaexam_template_loop_exam_category() {
		global $exam;
		
		echo $exam->get_category();
		
	}
}

if (  ! function_exists( 'yaexam_template_loop_exam_title' ) ) {
	
	function yaexam_template_loop_exam_title() {
		global $exam;
		
		echo '<div class="em-card-body">
			<h3 class="item-title">
				<a href="' . esc_url( get_permalink( $exam->id ) ) . '" title="' . esc_attr( $exam->get_title() ) . '">' . get_the_title() . '</a>
				
			</h3>';
			
		do_action('after_yaexam_template_loop_exam_title');
	}
}

	
function yaexam_template_loop_exam_excerpt() {
	
	echo '<div class="item-excerpt">' . get_the_excerpt() . '</div>';
	
}


function yaexam_template_loop_exam_action() {
	global $exam;

	$html = '<div class="item-action">'
			. '<a href="' . get_the_permalink() . '" class="em-btn em-btn-primary em-btn-start">' . __('Detail', 'yaexam') . '</a>';

	if(get_option('yaexam_is_rating') == 'yes') {

		$rating = $exam->get_rating();

		$html .= aws_rating_html( $rating['star'], $rating['users'] );
		
	}
	
	$html .= '</div>';
	
	echo $html;	
}

	
function yaexam_page_title( $echo = true ) {

	if ( is_search() ) {
		$page_title = sprintf( __( 'Search Results: &ldquo;%s&rdquo;', 'yaexam' ), get_search_query() );

		if ( get_query_var( 'paged' ) )
			$page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'yaexam' ), get_query_var( 'paged' ) );

	} elseif ( is_tax() ) {
		
		$page_title = single_term_title( "", false );
		
	} else {

		$exam_page_id = yaexam_get_page_id( 'archive_exam' );
		$page_title   = get_the_title( $exam_page_id );

	}

	$page_title = apply_filters( 'yaexam_page_title', $page_title );

	if ( $echo )
		echo $page_title;
	else
		return $page_title;
}



function yaexam_template_loop_exam_info() {
	
	global $exam;
			
	$duration	=	$exam->get_duration();
	$created	=	$exam->get_date_created();
	
	echo '<div class="item-info">'.
			'<div class="duration"><i class="material-icons">query_builder</i><span>' . $duration . '</span></div>'.
			'<div class="doing"><i class="material-icons">query_builder</i><span>' . $created . '</span></div>'.
		'</div>';

}



function yaexam_template_loop_exam_thumbnail() {
	$thumbnail	=	yaexam_get_exam_thumbnail();
	
	if($thumbnail){
		
		echo '<div class="item-thumbnail">' . $thumbnail . '</div>';
	}
}


	
function yaexam_get_exam_thumbnail( $size = 'shop_catalog', $deprecated1 = 0, $deprecated2 = 0 ) {
	global $post;
	
	if ( has_post_thumbnail() ) {
		return get_the_post_thumbnail( $post->ID, $size );
	}
	
	return false;
}


	
function yaexam_template_single_exam_start_title() {
	yaexam_get_template( 'single-exam/start/title.php' );
}


	
function yaexam_template_single_exam_start_thumbnail() {
	yaexam_get_template( 'single-exam/start/thumbnail.php' );
}

	
function yaexam_template_single_exam_result_head() {
	global $exam;
	
	yaexam_get_template( 'single-exam/result/head.php' );
}



function yaexam_template_single_exam_result_summary() {
	
	global $exam;

	$user_id 	=	get_current_user_id();

	if( !$user_id ) { return; }

	$result_id	=	absInt(get_query_var('em-result'));

	$args 		= 	[];
		
	$result = yaexam_get_result( $result_id );

	$exam_id	=	$result['exam_id'];

	$questions = yaexam_get_result_user_questions( $result_id );

	$settings = yaexam_exam_get_settings( $exam_id );

	$args = [
		'result_id' => $result_id,
		'score' => $result['score'],
		'total_score' => $result['total_score'],
		'corrects' => $result['total_corrects'],
		'wrongs' => $result['total_wrongs'],
		'notanswereds' => $result['total_notanswereds'],
		'date_start' => (int)yaexam_get_date($result['date_start'])->format('U'),
		'date_end' => (int)yaexam_get_date($result['date_end'])->format('U'),
		'exam_duration' => (int)$result['exam_duration'],
		'questions' => $questions,
		'result' => $result,
		'settings' => $settings,
	];


	yaexam_get_template( 'single-exam/result.php', $args );			
}



function yaexam_template_before_show_result_is_guest( $is_view = true, $result_id, $exam_id ) {
	
	$user_id 		=	get_current_user_id();

	$user = wp_get_current_user();

	$is_admin = false;

	if ( in_array( apply_filters('yaexam_user_roles_can_view_result', 'administrator', $user->roles), (array) $user->roles ) ) {
		
		$is_admin = true;
	}

	$tblResult      =	new yaexam_Table_Result();

	$result = $tblResult->load( $result_id );
	
	if( !isset($result['user_id']) || (($result['user_id'] != $user_id) && !$is_admin)  ) {

		wp_redirect( esc_url( get_permalink( $exam_id ) ) );
		exit;
	}
	
}




function yaexam_template_remove_guest( $params = array() ) {

	$user_id 	=	get_current_user_id();

	$is_guest 	=	get_user_meta( $user_id, 'is_guest', true );

	if( $is_guest ) {

		require_once(ABSPATH . 'wp-admin/includes/user.php' );

		wp_logout();

		yaexam_remove_results( 'user', $user_id );

		wp_delete_user( $user_id );
	}
}



function yaexam_template_before_show_result_options_certificate( $result_id ) {
	
	
	return true;
}



function yaexam_template_single_exam_title() {
	
	yaexam_get_template( 'single-exam/title.php');
}



function yaexam_template_single_exam_content() {
	
	yaexam_get_template( 'single-exam/content.php' );
}


function yaexam_template_single_exam_ranking() {
	global $exam;
	
	$settings	=	$exam->get_settings();
	
	if( isset($settings['is_ranking']) && $settings['is_ranking'] == 1) {
		
		$results	=	$exam->get_lasexam_results( array('order' => 'r.score DESC' ) );
		
		if($results){
			yaexam_get_template( 'single-exam/ranking.php', array( 'results' => $results, 'settings' => $settings ));
		}
	}
}



function yaexam_template_single_exam_review() {
	global $exam;
	
	$settings	=	$exam->get_settings();
			
	if(isset($settings['is_reviews']) && $settings['is_reviews'] == 1) {
		yaexam_get_template( 'single-exam/review.php');
	}
}



function yaexam_template_single_exam_start() {
	global $exam;
	
	$params	=	array(
		'user_id'			=>	get_current_user_id(),
		'is_can_do_exam'	=>	yaexam_can_do_exam( $exam->id ),
		'exam_id'			=>	$exam->id,
		'settings'			=>	$exam->get_settings()
	);

	$params['exam_info']	=	apply_filters( 'yaexam_single_exam_data_info', array(

			array( 
				'type'		=>	'text',
				'label'		=>	__('Duration', 'yaexam'), 
				'value'	 	=> $exam->get_duration() ),
			array( 
				'type'		=>	'text',
				'label'		=>	__('Attempt', 'yaexam'), 
				'value'		=> $exam->get_attempt() ),
			array( 
				'type'		=>	'text',
				'label'		=>	__('Questions', 'yaexam'), 
				'value'		=>	count($exam->get_questions())),

		), $exam->id );

	if( $captcha = is_yaexam_enable_captcha() ){

		array_push( $params['exam_info'], array( 'type' => 'recaptcha', 'value' => $captcha['key'] ) );
	}

	if( get_option('yaexam_is_rating') == 'yes' ) {

		array_push( $params['exam_info'], array( 'type' => 'rating', 'label' =>	__('Rating', 'yaexam'), 'value' => $exam->get_rating() ) );
	}
	
	yaexam_get_template( 'single-exam/start.php', $params );
}


	
function yaexam_template_single_exam_start_data_info_adaptive( $exam_info, $exam_id ) {
	
	$exam	=	new YAEXAM_Exam( $exam_id );

	if( $exam->get_type() == 1 ){

		$settings	=	$exam->settings;

		$max_round 	=	$settings['adaptive_max_round'] ? $settings['adaptive_max_round'] : __('unlimited', 'yaexam');

		array_push( $exam_info, 
			array( 'type' => 'text', 'label' => __('Corrected Times', 'yaexam'), 'value' => $settings['adaptive_times'] ),
			array( 'type' => 'text', 'label' => __('Max Round', 'yaexam'), 'value' => $max_round )
		);
	}

	return $exam_info;
}



function yaexam_template_single_exam_start_data_info_infinite( $exam_info, $exam_id ) {
	
	$exam	=	new yaexam_Exam( $exam_id );

	if( $exam->get_type() == 2 ){

		array_push( $exam_info, 
			array( 'type' => 'text', 'label' => __('Playing', 'yaexam'), 'value' => __('Correcting all questions', 'yaexam') )
		);

	}

	return $exam_info;
}



function yaexam_template_single_exam_start_exam_info( $exam_info, $exam_id ){
	
	if( is_array($exam_info) && count($exam_info) > 0 ){

		yaexam_get_template( 'single-exam/start-info.php', array( 'exam_info' => $exam_info ) );

	}
}



function yaexam_template_exam_start() {
	
	if(yaexam_can_do_exam()){
			
		yaexam_get_template('content-single-exam-start.php');
	}else{
		yaexam_get_template('single-exam/start/error.php');
	}
}



function yaexam_template_exam_doing() {
	
	$user_session   = yaexam_get_user_session( yaexam_clean($_GET['em-doing']) );
	$user_questions = yaexam_get_user_question_by_session( yaexam_clean($_GET['em-doing']) );

	$type_examing	=	yaexam_get_type_examing( yaexam_clean_int($user_session['exam_id']) );

	yaexam_get_template('content-single-exam-doing-' . $type_examing . '.php', array(
		'user_session'   => $user_session,
		'user_questions' => $user_questions
	));
}


function is_enable_review() {
	global $wp_the_query;
	
	return isset($wp_the_query->query_vars['em-result']);
}
	
function yaexam_login_form( $args = array() ) {

	$defaults = array(
		'message'  => '',
		'redirect' => '',
		'hidden'   => false
	);

	$args = wp_parse_args( $args, $defaults  );

	yaexam_get_template( 'myaccount/form-login.php', $args );
}


	
function yaexam_template_before_my_account( $args = array() ) {
	
	global $wp_the_query;
	
	$defaults = apply_filters('yaexam_template_my_account_link_tabs', array(
		'view_results_link'			=>	yaexam_get_endpoint_url( 'view-results', '', yaexam_get_page_permalink( 'myaccount' ) ),
		'view_save_laters_link'		=>	yaexam_get_endpoint_url( 'view-save-laters', '', yaexam_get_page_permalink( 'myaccount' ) ),
		'view_edit_account_link'	=>	yaexam_get_endpoint_url( 'view-edit-account', '', yaexam_get_page_permalink( 'myaccount' ) ),
	));


	
	$tabs	=	apply_filters('yaexam_template_my_account_tabs', array(
		'view-edit-account',
		'view-save-laters',
		'view-results' => array('view-result'),
		'view-certificates' => array('view-certificate'),
	));

	if(!is_array($args)) {

		$args = array();
	}

	foreach( $tabs as $link => $sub_link ) {
		
		if(!isset($args['active'])){
			
			if(is_array($sub_link)) {
			
				if(isset($wp_the_query->query_vars[$link])){
					$args['active']	=	$link;
				}
			
				if(!isset($args['active'])){
					foreach($sub_link as $value){
						if(isset($wp_the_query->query_vars[$value])){
							$args['active']	=	$link;
						}
					}
				}
			
			}else{
				
				if(isset($wp_the_query->query_vars[$sub_link])){
					$args['active']	=	$sub_link;
				}
			}

		}
		
	}
	
	$args = wp_parse_args( $args, $defaults  );
	
	yaexam_get_template( 'myaccount/tabs-start.php', $args );
}



function yaexam_template_after_my_account( $args = array() ) {
	
	yaexam_get_template( 'myaccount/tabs-end.php' );
}



function yaexam_template_my_account_tabs_title( $args ) {

	if( $args ){
		// var_dump($args); exit;
		foreach( $args as $tab_title => $tab_value ) {

			if( $tab_title !== 'active' )
			yaexam_get_template( 'myaccount/tabs_title/' . str_replace('_link', '', $tab_title) . '.php', $args );
		}
	}
}



function yaexam_template_my_account_user_info( $args ) {

	$user_id = get_current_user_id();


	$user_score = apply_filters( 'yaexam_template_my_account_user_info_score', yaexam_get_user_score($user_id) );

	?>

<div class="em-user-info mb-3">
		
	<div class="user-info-avatar mb-3">
		<i class="material-icons">person</i>
	</div>

	<?php if(get_option('yaexam_is_user_score') == 'yes'): ?>
	<div class="row">

		<div class="col-sm-12">
			
			<div class="user-info-intro">
				<span class="lbl"><?php _e('Score', 'yaexam'); ?></span>
				<span class="vl"><?php echo apply_filters( 'yaexam_template_my_account_user_info_score', yaexam_get_user_score($user_id) ); ?></span>
			</div>

		</div>

		
	</div>
	<?php endif; ?>
	

</div>
		

	<?php
}

function yaexam_email_result( $result ) {
	
	do_action( 'yaexam_email_new_result', $result );
	
}



function yaexam_template_update_user_cert_id( $result_id, $exam_id ) {
		
	$result = yaexam_get_result( $result_id );

	if( yaexam_is_ranking( $exam_id, $result['score'], $result['total_score'] ) ) {

		yaexam_update_result_cert_id( $result_id );
	}
}



function get_exam_search_form( $echo = true  ) {
	ob_start();

	do_action( 'pre_get_exam_search_form'  );

	yaexam_get_template( 'exam-searchform.php' );

	$form = apply_filters( 'get_exam_search_form', ob_get_clean() );

	if ( $echo ) {
		echo $form;
	} else {
		return $form;
	}
}



function yaexam_template_single_result_rating( $exam_id, $result_id ) {

	if( get_option('yaexam_is_rating') == 'yes' ) {

		$exam = new yaexam_Exam( $exam_id );
		
		$star = $exam->get_rating( get_current_user_id() );

		if( is_user_logged_in() ) {
			
			echo '<div class="item">'
				. '<div class="item-label">' . __('Rating', 'yaexam') . ': </div>'
				. '<div class="item-value" id="em-rating-wrapper"><star-rating value="' . $star . '" v-on:change="rateExam"></star-rating></div>'
			. '</div>';

		}

	}
}



function yaexam_template_header_doing( $doing_data ) {

	yaexam_get_template( 'single-exam/doing/header.php', array('doing_data' => $doing_data) );
}



function yaexam_template_navigate_doing( $doing_data ) {
		
	yaexam_get_template( 'single-exam/doing/navigate.php', array('doing_data' => $doing_data) );
}
