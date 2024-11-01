<?php

use YaExam\Tables\YAEXAM_Table_Exam_Question;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function yaexam_get_post_str( $post, $name, $default = '' ) {

	return isset($post[$name]) ? yaexam_clean(wp_unslash($post[$name])) : $default;
}

function yaexam_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {

	global $wpdb;

	$option_value     = get_option( $option );

	if ( $option_value > 0 ) {
		$page_object = get_post( $option_value );

		if ( 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ) ) ) {
			// Valid page is already in place
			return $page_object->ID;
		}
	}

	if ( strlen( $page_content ) > 0 ) {
		// Search for an existing page with the specified page content (typically a shortcode)
		$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
	} else {
		// Search for an existing page with the specified page slug
		$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
	}

	$valid_page_found = apply_filters( 'yaexam_create_page_id', $valid_page_found, $slug, $page_content );

	if ( $valid_page_found ) {
		if ( $option ) {
			update_option( $option, $valid_page_found );
		}
		return $valid_page_found;
	}

	// Search for a matching valid trashed page
	if ( strlen( $page_content ) > 0 ) {
		// Search for an existing page with the specified page content (typically a shortcode)
		$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
	} else {
		// Search for an existing page with the specified page slug
		$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
	}

	if ( $trashed_page_found ) {
		$page_id   = $trashed_page_found;
		$page_data = array(
			'ID'             => $page_id,
			'post_status'    => 'publish',
		);
	 	wp_update_post( $page_data );
	} else {
		$page_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => 1,
			'post_name'      => $slug,
			'post_title'     => $page_title,
			'post_content'   => $page_content,
			'post_parent'    => $post_parent,
			'comment_status' => 'closed'
		);
		$page_id = wp_insert_post( $page_data );
	}

	if ( $option ) {
		update_option( $option, $page_id );
	}

	return $page_id;
}

function yaexam_get_total_tests() {
	
	$count_tests	=	wp_count_posts('exam');
	
	$total			=	$count_tests->publish ? $count_tests->publish : 0;
	
	return $total;
}

function yaexam_get_total_questions() {
	
	return yaexam_count_questions();
}

function yaexam_get_total_members() {
	
	$count_users = count_users();
	
	if(isset($count_users['avail_roles']['yaexam_member'])){
		$members_role	=	$count_users['avail_roles']['yaexam_member'];
	}else{	
		$members_role	=	0;
	}
	
	return $members_role;
}

function yaexam_get_lastest_members( $order_by = 'registered' ) {
	
	$members	=	get_users(array('role' => 'yaexam_member', 'order_by' => $order_by, 'order' => 'DESC', 'number' => 10, 'paged' => 1));
	
	return $members;
}

function yaexam_get_lastest_results( $params = array() ) {
	
	global $wpdb;
	
	$params	=	wp_parse_args( $params, array('limit' => 10) );
	
	$result_tbl	=	$wpdb->prefix . 'yaexam_results';
	$user_tbl	=	$wpdb->prefix . 'users';
	
	$query	=	$wpdb->prepare('SELECT r.id, r.exam_id, r.user_id, r.score, r.total_score, r.percent, r.duration, r.date_start, r.date_end, u.user_nicename, u.user_login '.
			'FROM ' . $result_tbl . ' r LEFT JOIN ' . $user_tbl . ' u ON u.id = r.user_id '.
			'WHERE r.date_start IN(SELECT max(dr.date_start) FROM ' . $result_tbl . ' dr GROUP BY dr.user_id) '.
			'GROUP BY r.user_id ORDER BY date_start DESC LIMIT %d', $params['limit']);
	
	$results	= $wpdb->get_results($query, ARRAY_A);
	
	if($results){
		
		$results	=	yaexam_format_results( $results );
		
	}
	
	return $results;
}

function yaexam_get_sessions( $args = array() ) {
	
	global $wpdb;

	$params	=	wp_parse_args( $args, array('limit' => 10) );
	
	$sessions	=	$wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'yaexam_sessions ORDER BY session_expiry DESC LIMIT ' . absint($params['limit']), 'ARRAY_A' );
	
	if($sessions) {
		$data				=	array();
		$expiry_sessions	=	array();
		
		foreach($sessions as $session){
			
			if(isset($session['session_value'])){
				
				$session_data	=	unserialize( $session['session_value'] );
		
				$sdata			=	array();
				
				if(isset($session_data['doing']) && $session_data['doing']) {
					
					$doing_data		=	unserialize($session_data['doing']);
					
					$is_session_expiry	=	yaexam_is_expired($session['session_expiry'], yaexam_time(), 169200);
					$is_test_expiry		=	$doing_data['duration'] > 0 && yaexam_is_expired($doing_data['time_start'], yaexam_time(), $doing_data['duration']);
					
					if( !$is_session_expiry && !$is_test_expiry){
					
						$user			=	new WP_User( $doing_data['uid'] );
						$test			=	new QM_Test( $doing_data['tid'] );
					
						$user_admin_link	=	'<a href="' . admin_url('user-edit.php?user_id=' . $user->ID) . '">' . $user->user_login . '</a>';
						$test_admin_link	=	'<a href="' . admin_url('post.php?action=edit&post=' . $test->get_id()) . '">' . $test->get_title() . '</a>';
					
						array_push($data, array(
							'user'			=>	$user_admin_link,
							'test_link'		=>	$test_admin_link,
							'test_duration'	=>	$test->get_duration()
						));
					}else{
						
						array_push($expiry_sessions, $session['session_id']);
					}
				}
				
			}
		}
		
		if($expiry_sessions) {
			yaexam_destroy_session( $expiry_sessions );
		}
		
		return $data;
	}
		
	return array();
}

function yaexam_destroy_session( $session_id ) {
	global $wpdb;
	
	if(!isset($session_id)) return false;
	
	$ids	=	absint($session_id);
		
	if(is_array($session_id)) {
		$ids	=	implode(',', array_map('absint', $session_id));
	}
	
	$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'yaexam_sessions' . ' WHERE session_id IN(' . $ids . ')' );
}

function yaexam_dropdown_posts( $args, $attributes = '', $selected = 0, $echo = true ) {
	
	$args	=	wp_parse_args( $args, array( 'posts_per_page' => -1 ) );
	
	$posts	=	get_posts( $args );
	
	$output	=	'<select ' . $attributes . '>';

	$output .= '<option value="0"' . selected($selected, 0, false) . '>' . __('None', 'yaexam') . '</option>';
	
	if($posts){
		foreach($posts as $post){
			$output .= '<option value="' . $post->ID . '" ' . selected($selected, $post->ID, false) . '>' . $post->post_title . '</option>';
		}
	}
	
	$output .= '</select>';
	
	if($output){
		echo $output;
	}else{
		return $output;
	}
}

function YAEXAM_Admin_ranking_link( $post_id, $echo = true ){
	
	if(!$post_id) return false;
	
	$link	= '<a href="' . admin_url('post.php?post=' . $post_id . '&action=edit') . '">' . get_the_title($post_id) . '</a>';
	
	if( $echo ) {
		echo $link;
	}else{
		
		return $link;
	}
}

function yaexam_setting_display( $params ) {

	$type = isset($params['type']) ? $params['type'] : false;

	switch( $type ) {
		case 'text':
			yaexam_setting_display_text_input($params);
		break;
		case 'number':
			yaexam_setting_display_number_input($params);
		break;
		case 'radiobox':
			yaexam_setting_display_radiobox($params);
		break;
		default:
			yaexam_setting_display_checkbox($params);
		break;
	}
}

function yaexam_setting_display_checkbox( $params ) {
	?>

	<tr>
		<th scope="row" style="width:300px;">
			<label for="yaexam_setting_<?php echo $params['id']; ?>"><?php echo $params['label']; ?></label>
		</th>
		<td>
			<input id="yaexam_setting_<?php echo $params['id']; ?>" 
				type="checkbox" name="<?php echo $params['name']; ?>" 
				<?php checked('yes', $params['value']); ?>/>
		</td>
	</tr>

	<?php
}

function yaexam_setting_display_text_input( $params ) {

	$attrs = isset($params['attrs']) ? $params['attrs'] : '';
	?>

	<tr>
		<th scope="row" style="width:300px;">
			<label><?php echo $params['label']; ?></label>
		</th>
		<td>
			<input type="text" name="<?php echo $params['name']; ?>" value="<?php echo $params['value']; ?>" <?php echo $attrs; ?> />
		</td>
	</tr>

	<?php
}

function yaexam_setting_display_number_input( $params ) {

	$attrs = isset($params['attrs']) ? $params['attrs'] : '';
	?>

	<tr>
		<th scope="row" style="width:300px;">
			<label><?php echo $params['label']; ?></label>
		</th>
		<td>
			<input type="number" name="<?php echo $params['name']; ?>" value="<?php echo $params['value']; ?>" <?php echo $attrs; ?> />
		</td>
	</tr>

	<?php
}

function yaexam_setting_display_radiobox( $params ) {
	?>

	<tr>
		<th scope="row" style="width:300px;">
			<label><?php echo $params['label']; ?></label>
		</th>
		<td>
			<?php if(isset($params['options']) && $params['options']):?>
				<?php foreach($params['options'] as $option):?>
				<label class="em-mr-3">
					<input type="radio" value="<?php echo $option['value']; ?>" name="<?php echo $params['name']; ?>" <?php checked($option['value'], $params['value']); ?> />
					<span><?php echo $option['label']; ?></span>
				</label>
				<?php endforeach;?>
			<?php endif;?>
		</td>
	</tr>

	<?php
}

function yaexam_count_questions() {

	global $wpdb;

	return $wpdb->get_var('SELECT COUNT(id) AS total FROM ' . $wpdb->prefix . 'yaexam_questions');
}

function yaexam_get_paginated_questions( $args = array() ) {

	$args 	=	wp_parse_args( $args, array( 'perpage' => 50, 'page' => 1, 'category' => 0 ) );
	
	$page 		=	yaexam_clean_int( $args['page'] );
	$perpage	=	yaexam_clean_int( $args['perpage'] );
	$category	=	yaexam_clean_int( $args['category'] );

	$total 		=	yaexam_count_questions();
	$pages 		= 	ceil( $total/$perpage );

	$data = yaexam_get_questions([
		'category'	  => $category,
		'limit'	  => $perpage,
		'offset'  => ($page - 1) * $perpage
	]);

	return [
		'data'  => $data,
		'total' => $total,
		'pages' => $pages,
		'page'	=> $page
	];
}

function yaexam_remove_questions( $ids ) {

	global $wpdb;

	if( !is_array( $ids ) ) {

		$ids = [$ids];
	}

	$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'yaexam_questions WHERE id IN ' . '(' . implode(', ', $ids) . ')' );

	$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'yaexam_exam_questions WHERE question_id IN ' . '(' . implode(', ', $ids) . ')' );

	$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'yaexam_user_questions WHERE question_id IN ' . '(' . implode(', ', $ids) . ')' );
}

function yaexam_remove_categories( $ids ) {

	global $wpdb;

	if( !is_array( $ids ) ) {

		$ids = [$ids];
	}

	return $wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'yaexam_question_categories WHERE id IN ' . '(' . implode(', ', $ids) . ')' );
}

function yaexam_get_question_categories() {

	global $wpdb;

	return $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'yaexam_question_categories', ARRAY_A);
}

function yaexam_html_select_categories( $selected ) {

	$categories = yaexam_get_question_categories();

	?>

	<select name="category">
		<option value="0"><?php esc_html_e('Select a category', 'yaexam') ?></option>
		<?php foreach($categories as $cat): ?>
		<option value="<?php echo $cat['id']; ?>" <?php selected( $selected, $cat['id'], true ); ?>><?php echo $cat['name'] ?></option>
		<?php endforeach; ?>
	</select>

	<?php
}

function yaexam_html_select_exams( $selected ) {

	$exams = yaexam_get_exams();
	
	if( !$exams ) { return; }

	?>

	<select name="exam">
		<option value="0"><?php esc_html_e('Select a exam', 'yaexam') ?></option>
		<?php foreach($exams as $item): ?>
		<option value="<?php echo $item->ID; ?>" <?php selected( $selected, $item->ID, true ); ?>><?php echo $item->post_title; ?></option>
		<?php endforeach; ?>
	</select>

	<?php 
}
 
function yaexam_add_fixed_questions( $id, $questions ) {
	
	if( !$questions ){ return false; }
		
	$ids_1 	=	yaexam_get_post_meta( $id, 'fixed_questions' );
	
	$ids_1		=	$ids_1 ? $ids_1 : array();

	$fixed_questions = array_unique(array_merge( $questions, $ids_1 ));
	
	yaexam_update_post_meta( $id, array( 'fixed_questions' => $fixed_questions ) );
	
	return $fixed_questions;
}

function yaexam_remove_fixed_questions( $id, $questions ) {
	
	if( !$questions ){ return false; }

	$ids_1 	=	yaexam_get_post_meta( $id, 'fixed_questions' );
		
	$ids_1	=	$ids_1 ? $ids_1 : array();
	
	$fixed_questions = array();

	if($ids_1){

		$fixed_questions	=	array_diff($ids_1, $questions);
	}
	
	return yaexam_update_post_meta( $id, array('fixed_questions' => $fixed_questions) );
}

function yaexam_update_fixed_questions( $id, $questions ) {

	$questions = is_array($questions) ? $questions : [];

	return yaexam_update_post_meta( $id, array('fixed_questions' => $questions) );
}

function yaexam_get_paginated_fixed_questions( $id, $args ) {

	$args 	=	wp_parse_args( $args, array( 'perpage' => 20, 'page' => 1 ) );

	$ids 	=	yaexam_get_post_meta( $id, 'fixed_questions' );

	if( !$ids ) { return []; }
	
	$total 		=	count( $ids );
	$page 		=	absInt( $args['page'] );
	$perpage	=	$args['perpage'];
	$pages 		= 	ceil( $total/$perpage );

	$data = yaexam_get_questions([
		'include' => $ids,
		'limit'	  => $perpage,
		'offset'  => ($page - 1),
		'orderby' => 'include'
	]);
	
	return [
		'data'  => $data,
		'pages' => $pages,
		'page'	=> $page
	];
}

function yaexam_html_ui_switch_number( $args ) { ?>

	<div class="groups-field">
		<div class="group-field">
			<label><?php echo $args['label'] ?></label>
			<div class="field-input">
				
				<div class="qm-input-switch-number">
					<div class="qm-input-switch-number-input">
		
						<div class="qm-input-switch-number-input-slide">
							<div class="on-block"><?php _e('ON', 'yaexam'); ?></div>
							<div class="off-block"><?php _e('OFF', 'yaexam'); ?></div>
						</div>
		
						<div class="qm-input-switch-number-input-off"><?php _e('OFF', 'yaexam'); ?></div>
		
						<div class="qm-input-switch-number-input-on">
							<input type="text" name="<?php echo $args['name'] ?>" data-off="0" value="<?php echo $args['value'] ?>"/>
						</div>
		
					</div>
					
					<?php if(isset($args['desc_on']) || isset($args['desc_off'])): ?>
					<div class="qm-input-switch-number-info">

						<?php if(isset($args['desc_on'])): ?>
						<span class="qm-input-switch-number-info-on"><?php echo $args['desc_on']; ?></span>
						<?php endif; ?>

						<?php if(isset($args['desc_off'])): ?>
						<span class="qm-input-switch-number-info-off"><?php echo $args['desc_off']; ?></span>
						<?php endif; ?>

					</div>
					<?php endif; ?>

				</div>
				
			</div>
		</div>
	</div>

	<?php 
}

function yaexam_html_ui_switch_select( $args ) { ?>

	<div class="groups-field">
		<div class="group-field">
			<label><?php echo $args['label'] ?></label>
			<div class="field-input checkbox">
				
				<div class="qm-input-switch-select">
					<div class="qm-input-switch-select-input">
		
						<div class="qm-input-switch-select-input-slide">
							<div class="on-block"><?php _e('ON', 'yaexam'); ?></div>
							<div class="off-block"><?php _e('OFF', 'yaexam'); ?></div>
						</div>
		
						<div class="qm-input-switch-select-input-off"><?php _e('OFF', 'yaexam'); ?></div>
		
						<div class="qm-input-switch-select-input-on">
							<span><?php _e('ON', 'yaexam'); ?></span>
							
							<input type="hidden" name="<?php echo $args['name'] ?>" data-off="0" data-on="1" value="<?php echo isset($args['value']) && $args['value'] ? 1 : 0; ?>"/>
						</div>
						
					</div>

					<?php if(isset($args['desc_on']) || isset($args['desc_off'])): ?>
					<div class="qm-input-switch-number-info">

						<?php if(isset($args['desc_on'])): ?>
						<span class="qm-input-switch-number-info-on"><?php echo $args['desc_on']; ?></span>
						<?php endif; ?>

						<?php if(isset($args['desc_off'])): ?>
						<span class="qm-input-switch-number-info-off"><?php echo $args['desc_off']; ?></span>
						<?php endif; ?>

					</div>
					<?php endif; ?>
					
				</div>
				
			</div>
		</div>
	</div>

<?php 
}

function yaexam_html_ui_input( $args ) { ?>

	<div class="groups-field">
		<div class="group-field">
			<label><?php echo $args['label'] ?></label>
			<div class="field-input checkbox">
				<input type="text" name="<?php echo $args['name'] ?>" value="<?php echo $args['value'] ?>" class="qm-s-1"/>
			</div>
		</div>
	</div>

<?php 
}

function yaexam_html_ui_select( $args ) { ?>

	<div class="groups-field">
		<div class="group-field">
			<label><?php echo $args['label'] ?></label>
			<div class="field-input checkbox">

				<select name="<?php echo $args['name'] ?>">

					<?php foreach( $args['options'] as $option ): ?>

					<option value="<?php echo $option['value']; ?>" <?php selected($args['value'], $option['value']); ?>><?php echo $option['name'] ?></option>

					<?php endforeach; ?>

				</select>

			</div>
		</div>
	</div>

<?php 
}

function yaexam_get_show_hide_class( $exam_types, $name, $default ) {

	$show_hide_classes = [$default];

	foreach( $exam_types as $type ) {
		$show_hide_classes = array_merge( $show_hide_classes, apply_filters('yaexam_show_hide_class_' . $type['name'] . '_' . $name, [$default]) );
	}

	return implode(' ', array_unique($show_hide_classes));
}

function yaexam_get_paginated_total_questions( $params ) {
	
    global $wpdb;
    
	$result_tbl	    =	$wpdb->prefix . 'yaexam_questions';
    
    $query  = $wpdb->prepare(
        'SELECT COUNT(id) FROM ' . $result_tbl);
   
	return $wpdb->get_var($query);	
}

function yaexam_get_paginated_question( $params = array() ){
		
	global $wpdb;

	$params = wp_parse_args( $params, array(
		'perPage' 	=> 20, 
		'page' 		=> 1, 
		'orderBy'	=>	[],
	) );
	
	$order 		=  [];

	foreach( $params['orderBy'] as $o ) {

		$order[] = yaexam_clean(wp_unslash($o['field'])) . ' ' . yaexam_clean(wp_unslash($o['order']));
	}

	$order = ' ORDER BY ' . implode(', ', $order);

	$result_tbl	=	$wpdb->prefix . 'yaexam_questions';

	$page 		=	absInt($params['page']);
	$perpage	=	absInt($params['perPage']);

	$total 		= 	yaexam_get_paginated_total_questions($params);
	
	$pages 		=	ceil($total / $perpage);
	$offset 	=	($page - 1) * $perpage;
    
	if($page > $pages){
		$page = 1;
	}

	$results = array();
	
	if( $total > 0 ){

        $query	=	$wpdb->prepare(
            'SELECT * FROM ' . $result_tbl . $order . ' LIMIT %d, %d'
            , $offset, $perpage);
		
		$results	= $wpdb->get_results($query, ARRAY_A);
	}

	return array( 
		'items' 	=> $results,
		'total' 	=> $total, 
		'pages' 	=> $pages, 
		'page' 		=> $page
	);
}

function yaexam_assign_questions( $exam_id, $questions ) {

	global $wpdb;

	if( !empty($questions) ) {

		$table = $wpdb->prefix . 'yaexam_exam_questions';

		$max_order = $wpdb->get_var($wpdb->prepare("SELECT MAX(question_order) AS max_order FROM {$table} WHERE exam_id = %d", $exam_id));

		$max_order = $max_order ? $max_order : 0;
		
		foreach( $questions as $question ) {

			$question_title = yaexam_clean(wp_unslash($question['title']));
			$question_id    = absint($question['id']);

			if( $question_id && $question_title ) {

				$table	=	new YAEXAM_Table_Exam_Question();

				$max_order++;

				$table->save([
					'exam_id' 			=> $exam_id,
					'question_id' 		=> $question_id,
					'title' 			=> $question_title,
					'question_type'		=> 'question',
					'question_order'	=>	$max_order
				]);
			}
		}

		return true;
	}

	return false;
}

function yaexam_assign_categories( $exam_id, $questions ) {
    
	if( !empty($questions) ) {

		foreach( $questions as $question ) {

			$question_title = yaexam_clean(wp_unslash($question['title']));
			$question_id    = absint($question['id']);

			if( $question_id && $question_title ) {

				$table	=	new YAEXAM_Table_Exam_Question();
				
				$question_total = absint($question['total']);

				$table->save([
					'exam_id' 			=> absint($exam_id),
					'question_id' 		=> $question_id,
					'title' 			=> $question_title,
					'question_type'		=> 'category',
					'question_params' 	=> json_encode(['total' => $question_total]),
				]);
			}
		}

		return true;
	}

	return false;
}

function yaexam_unassign_questions( $questions ) {
	
	global $wpdb;

	if( !empty($questions) ) {
		$where = 'id IN (' . implode(', ', yaexam_clean_int($questions)) . ')';

		$wpdb->query('DELETE FROM ' . $wpdb->prefix . 'yaexam_exam_questions WHERE ' . $where);

		return true;
	}

	return false;
}

function yaexam_sort_questions( $exam_id, $questions ) {

	global $wpdb;

	if( !empty($questions) ) {

		$table = $wpdb->prefix . 'yaexam_exam_questions';

		$question_replaces = [];

		foreach( $questions as $id => $index ) {
			
			$question_replaces[] = 'WHEN id = ' . yaexam_clean_id($id) . ' THEN ' . yaexam_clean_id($index);
		}

		$replace_values =  implode(' ', $question_replaces);

		$exam_id = yaexam_clean_id($exam_id);

		return $wpdb->query("UPDATE {$table} SET question_order = (CASE " . $replace_values . " END) WHERE exam_id = {$exam_id}");
	}
}

function yaexam_update_exam_question( $id, $data, $format ) {
	global $wpdb;
	
	$table = $wpdb->prefix . 'yaexam_exam_questions';

	return $wpdb->update($table, $data, ['id' => $id], $format, ['%d']);
}

function yaexam_admin_get_exam_question($id) {
	global $wpdb;

	$item = $wpdb->get_row(
		$wpdb->prepare("SELECT * FROM {$wpdb->prefix}yaexam_exam_questions WHERE id = %d", $id), ARRAY_A);

	if( $item ) {
		if( $item['question_type'] == 'category' ){
			$item['question_params'] = json_decode($item['question_params'], true);
		}	
	}

	return $item;
}

function yaexam_admin_get_exam_questions($exam_id) {
	global $wpdb;
	
	$items = $wpdb->get_results(
		$wpdb->prepare("SELECT * FROM {$wpdb->prefix}yaexam_exam_questions WHERE exam_id = %d ORDER BY question_order ASC", $exam_id), ARRAY_A);

	if( $items ) {
		foreach( $items as &$item ) {
			if( $item['question_type'] == 'category' ){
				$item['question_params'] = json_decode($item['question_params'], true);
			}
		}
	}

	return $items;
}
