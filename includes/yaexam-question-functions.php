<?php 

function yaexam_get_questions( $args ) {

	global $wpdb;

	$args = wp_parse_args( $args, [
		'id' => false,
		'category' => false,
		's' => '',
		'include' => [],
		'exclude' => [],
		'limit' => false,
		'offset' => 0,
		'orderby' => 'id',
		'order' => 'DESC',
		'where' => [],
	] );

	$query = 'SELECT * FROM ' . $wpdb->prefix . 'yaexam_questions';

	$where = $args['where'];

	if( $args['id'] ) {

		$where[] = $wpdb->prepare('id = %d', $args['id']);
	}

	if( $args['s'] ) {

		$where[] = $wpdb->prepare('title LIKE %s', '%' . yaexam_clean($args['s']) . '%');
	}

	if( $args['include'] ) {

		$include = implode(', ', yaexam_clean_int($args['include']));

		$where[] = 'id IN(' . $include . ')';
	}

	if( $args['exclude'] ) {

		$exclude = implode(', ', yaexam_clean_int($args['exclude']));

		$where[] = 'id NOT IN(' . $exclude . ')';
	}

	if( $args['category'] ) {

		$where[] = $wpdb->prepare('category_id = %d', $args['category']);
	}
	
	if( $where ) {

		$query .= ' WHERE ' . implode( ' AND ', $where );

	}

	if( $args['orderby'] == 'include' && $args['include'] ) {

		$orderby = $args['include'];
		array_unshift($orderby, 'id');
		
		$query .= ' ORDER BY field(' . implode(', ', $orderby) . ')';

	}elseif( $args['orderby'] == 'RAND()' ){

		$query .= ' ORDER BY RAND()';

	}else{

		$query .= ' ORDER BY ' . $args['orderby'] . ' ' . $args['order'];
	}

	if( $args['limit'] ) {

		$query .= $wpdb->prepare(' LIMIT %d OFFSET %d', $args['limit'], $args['offset']);
	}



	return $wpdb->get_results( $query, ARRAY_A );
}

function yaexam_get_fixed_categories( $args = [] ) {

	global $wpdb;

	$args = wp_parse_args( $args, [
		'id' => false,
		's' => '',
		'include' => [],
		'exclude' => [],
		'limit' => false,
		'offset' => 0,
		'orderby' => 'id',
		'order' => 'DESC',
		'where' => [],
	] );

	$query = 'SELECT * FROM ' . $wpdb->prefix . 'yaexam_question_categories';

	$where = $args['where'];

	if( $args['id'] ) {

		$where[] = $wpdb->prepare('id = %d', $args['id']);
	}

	if( $args['s'] ) {

		$where[] = $wpdb->prepare('name LIKE %s', '%' . $args['s'] . '%');
	}

	if( $args['include'] ) {

		$include = implode(', ', $args['include']);

		$where[] = 'id IN(' . $include . ')';
	}

	if( $args['exclude'] ) {

		$exclude = implode(', ', $args['exclude']);

		$where[] = 'id NOT IN(' . $exclude . ')';
	}

	if( $where ) {

		$query .= ' WHERE ' . implode( ' AND ', $where );

	}

	if( $args['orderby'] == 'include' && $args['include'] ) {

		$orderby = $args['include'];
		array_unshift($orderby, 'id');
		
		$query .= ' ORDER BY field(' . implode(', ', $orderby) . ')';

	}elseif( $args['orderby'] == 'RAND()' ){

		$query .= ' ORDER BY RAND()';

	}else{

		$query .= ' ORDER BY ' . $args['orderby'] . ' ' . $args['order'];
	}

	if( $args['limit'] ) {

		$query .= $wpdb->prepare(' LIMIT %d OFFSET %d', $args['limit'], $args['offset']);
	}

	return $wpdb->get_results( $query, ARRAY_A );
}

function yaexam_question_get_settings( $post_id ){
	
	$settings 	=	get_post_meta($post_id, '_question_data', true);
	
	$settings	=	$settings ? $settings : array();
	
	$settings	=	wp_parse_args($settings, array(
		'score'	=>	10
	));
	
	return $settings;
}
	
function yaexam_question_set_answers( $questions = array(), $shuffle = false ){
	if( !$questions ) return array();
	
	if($questions){
		foreach($questions as &$question){
			
			$question->score		=	yaexam_get_post_meta( $question->ID, 'score' );
			$question->answer_type	=	yaexam_get_post_meta( $question->ID, 'answer-type' );
			$question->answers		=	yaexam_get_post_meta( $question->ID, 'answers' );
			$question->order_type	=	yaexam_get_post_meta( $question->ID, 'order_type' );
			$question->explanation	=	yaexam_get_post_meta( $question->ID, 'explanation' );

			$question->params 		=	yaexam_get_post_meta( $question->ID, 'params_' . $question->answer_type );
		}
		
		return $questions;
	}
	
	return array();
}

function yaexam_question_get_categories( $params )
{
	$categories	=	get_terms(array(
		'taxonomy' 		=> 'question_cat',
		'hide_empty'	=>	false
	));
	
	if( $categories ){

		foreach( $categories as $cat ) {
			
			$questions	=	get_posts(array(
				'post_type'			=>	'question',
				'post_status'		=>	'publish',
				'suppress_filters' 	=> true,
				'posts_per_page'	=>	-1,
				'tax_query'	=> array(
					array(
						'taxonomy'	=>	'question_cat',
						'field'		=>	'id',
						'terms'		=>	$cat->term_id
					)
				)
			));
			
			if(is_object($cat)){
				$cat->total_questions	=	count($questions);
			}
		}

	}
	
	return $categories;
}

function yaexam_question_by_ids( $ids = array() ) {

	if(!$ids || !is_array($ids)) return false;
	
	return get_posts(array(
		'posts_per_page'	=>	-1,
		'post_type'			=>	'question',
		'post__in' 			=> $ids,
		'orderby'			=>	'post__in'
	));
}

function yaexam_get_question_by_categories( $cids = array(), $limit = -1 ) {

	if(!$cids || !is_array($cids)) return false;

	$questions	=	get_posts(array(
		'post_type'			=>	'question',
		'post_status'		=>	'publish',
		'orderby'        	=> 'rand',
		'suppress_filters' 	=> 	true,
		'posts_per_page'	=>	$limit,
		'tax_query'	=> array(
			array(
				'taxonomy'	=>	'question_cat',
				'field'		=>	'id',
				'terms'		=>	$cids,
				'operator'	=>	'IN'
			)
		)
	));

	return $questions;
}

function yaexam_add_question_categories( $exam_id )
{

	$selected_categories = array();

	$random_questions = get_post_meta( $exam_id, '_randoms_questions', true );
	$fixed_questions  = get_post_meta( $exam_id, '_fixed_questions', true );

	if( $random_questions )
	{
		$categories = $random_questions[ $random_questions['type'] ][ 'categories' ];

		if(isset($categories) && $categories){
			foreach( $categories as $key => $cat )
			{
				if($cat)
				{
					$selected_categories[] = $key;
				}
			}
		}
	}

	if( $fixed_questions )
	{
		$fixed_question_categories = array();

		foreach( $fixed_questions as $question_id )
		{
			$terms = get_the_terms( $question_id, 'question_cat' );

			if( $terms )
			{
				foreach( $terms as $term )
				{
					if( !in_array( $term->term_id, $selected_categories ) )
					{
						$selected_categories[] = $term->term_id;
					}


				}

				$fixed_question_categories[$question_id] = array('cat_id' => $terms[0]->term_id, 'cat_name' => $terms[0]->name);
			}
		}

		update_post_meta( $exam_id, '_fixed_question_categories', $fixed_question_categories );
	}

	update_post_meta( $exam_id, '_question_categories', $selected_categories );
}

function yaexam_question_get_fixed_categories( $exam_id ) {

	$categories = get_post_meta( $exam_id, '_fixed_question_categories', true );

	if( !$categories ) {

		yaexam_add_question_categories( $exam_id );

		return get_post_meta( $exam_id, '_fixed_question_categories', true );
	}

	return $categories;
}

function yaexam_question_get_fixed_items( $exam_id )
{
	$fids 	=	get_post_meta($exam_id, '_fixed_questions', true);
	
	$fixed_questions	=	array();

	if($fids)
	{
		// $fixed_questions	=	get_posts(array(
		// 	'posts_per_page'	=>	-1,
		// 	'post_type'			=>	'question',
		// 	'post__in' 			=> $fids,
		// 	'orderby'			=>	'post__in'
		// ));

		$fixed_questions = yaexam_get_questions(['include' => $fids, 'orderby' => 'include']);

		if($fixed_questions)
		{

			// $categories = yaexam_question_get_fixed_categories($exam_id);

			foreach( $fixed_questions as &$question )
			{

				$question['answers'] = json_decode( $question['answers'], true );
				$question['params']  = json_decode( $question['params'], true );

				// $question->params = array( 'id' => $question->ID );

				// if( $categories ){
				// 	foreach( $categories as $question_id => $category ) {
				// 		if( $question->ID == $question_id )
				// 		{
				// 			$question->params['cat_id']		= $category['cat_id'];
				// 			$question->params['cat_name'] 	= $category['cat_name'];
							
				// 		}
				// 	}	
				// }
			}
		}

	}
	
	return $fixed_questions;
}

function yaexam_question_get_random_items( $post_id )
{
	$randoms_questions 	=	yaexam_get_post_meta( $post_id, 'randoms_questions' );
	
	$randoms_questions	=	$randoms_questions ? $randoms_questions : array();
	
	$randoms_questions	=	wp_parse_args($randoms_questions, array(
		'type'	=>	'none',
		'selected' => array(
			'order' 		=>	'mixed',
			'position'		=>	'mixed_fquestions',
			'total'			=>	10,
			'categories'	=>	array()
		),
		'per'	=>	array(
			'order' 		=>	'mixed',
			'position'		=>	'mixed_fquestions',
			'categories'	=>	array()
		)
	));
	
	return $randoms_questions;
}

function yaexam_question_categories( $exam_id )
{
	if(!isset($exam_id) || !absInt($exam_id)){ return false; }

	$randoms_categories =	$this->yaexam_question_get_random_items( $exam_id );

	$fixed_questions 	=	$this->yaexam_question_get_fixed_items( $exam_id );

	
}

function yaexam_question_get_random_doing_items( $post_id, $fixed_questions = array() )
{
	global $wpdb;
	
	$questions			=	array();

	$fixed_questions 	=	(isset($fixed_questions) && $fixed_questions) ? $fixed_questions : array();
	$random_questions	=	yaexam_question_get_random_items($post_id);
	
	if($random_questions['type'] == 'none') {
		return $questions;
	}

	$random_se 	=	array();

	$cat_ids	=	array();
	
	if(isset($random_questions[$random_questions['type']])){
		$random_se		=	$random_questions[$random_questions['type']];
	}
		
	if( $random_questions['type'] == 'per' ){
		
		$random_cats	=	$random_se['categories'];
		
		$cat_ids	=	array_keys( $random_cats );

		$categories = 	yaexam_get_categories( $cat_ids );
				
		foreach( $categories as $cat_id => $cat_name ){
			
			if( absInt($random_cats[$cat_id]) ){

				// $question	=	get_posts(array(
				// 	'post_type'			=>	'question',
				// 	'post_status'		=>	'publish',
				// 	'orderby' 			=> 'rand',
				// 	'suppress_filters' 	=> true,
				// 	'posts_per_page'	=>	absInt($random_cats[$cat_id]),
				// 	'exclude'			=>	$fixed_questions,
				// 	'tax_query'	=> array(
				// 		array(
				// 			'taxonomy'	=>	'question_cat',
				// 			'field'		=>	'id',
				// 			'terms'		=>	$cat_id
				// 		)
				// 	)
				// ));

				$question = yaexam_get_questions([
					'exclude' 	  => $fixed_questions,
					'limit'   	  => absInt($random_cats[$cat_id]),
					'category_id' => $cat_id,
					'orderby'	  => 'RAND()'
				]);
				
				if($question)
				{
					// $excludes	=	array_merge( $fixed_questions, yaexam_get_values_by_key( $question, 'id' ) );

					foreach( $question as &$q )
					{ 
						
						// $q->params = array('id' => $q->ID, 'cat_id' => $cat_id, 'cat_name' => $cat_name);
						$q['answers'] = json_decode( $q['answers'], true );
						$q['params']  = json_decode( $q['params'], true );
						$q['params']['id'] = $q['id'];
						$q['params']['cat_id'] = $cat_id;
						$q['params']['cat_name'] = $cat_name;
					}

					$questions	=	array_merge( $questions, $question );
				}
				
			}
		}
		
		if($random_se['order'] == 'mixed'){
			shuffle($questions);
		}
		
	}elseif( $random_questions['type'] == 'selected' ){

		if( isset($random_se['categories']) && $random_se['categories'] )
		{
			
			$orderby = 'menu_order';

			if($random_se['order'] == 'mixed'){
				$orderby = 'rand';
			}

			// $questions	=	get_posts(array(
			// 	'post_type'			=>	'question',
			// 	'post_status'		=>	'publish',
			// 	'suppress_filters' 	=>  true,
			// 	'orderby'			=>	$orderby,
			// 	'order'				=>	'ASC',
			// 	'posts_per_page'	=>	$random_se['total'],
			// 	'numberposts'		=>	$random_se['total'],
			// 	'exclude'			=>	$fixed_questions,
			// 	'tax_query'	=> array(
			// 		array(
			// 			'taxonomy'	=>	'question_cat',
			// 			'field'		=>	'id',
			// 			'terms'		=>	array_values($random_se['categories'])
			// 		)
			// 	)
			// ));

			$random_selected_type_args = [
				'exclude' 	  => $fixed_questions,
				'limit'   	  => absInt($random_se['total']),
				'orderby'	  => 'RAND()'
			];

			if( $random_se['categories'] ) {

				$random_selected_type_args['where'] = [
					'category_id IN (' . implode(', ', array_values($random_se['categories']) ) . ')'
				];
			}

			$questions = yaexam_get_questions( $random_selected_type_args );
			// var_dump($fixed_questions, array_values($random_se['categories'])); exit;
			if($questions)
			{
				foreach( $questions as &$q )
				{ 
					$q['answers'] = json_decode( $q['answers'], true );
					$q['params']  = json_decode( $q['params'], true );
					$q['params']['id'] = $q['id'];
				}

			}

		}
	}
	
	if($questions)
	{

		$random_se['type']		=	$random_questions['type'];
		$random_se['questions']	=	$questions;
		
		return $random_se;
	}
	
	return array();
}

function yaexam_get_question_params( $question_id, $question_params, $name = false )
{
	if(!isset($question_params) || is_array($question_params) || $question_params ) { return false; }

	foreach( $question_params as $params )
	{
		if(isset($params['id']) && $params['id'] == $question_id)
		{
			if( $name ){
				return $params[$name];
			}else{
				return $params;
			}
		}
	}
}

function yaexam_is_single_answers_correct($answers, $correct) {
	
	$is_correct = 0;

	if( !is_numeric($correct) || is_array($correct) ){
		return 0;
	}
	
	foreach($answers as $id => $ans){
		
		if( ($ans['is_correct'] == 1) && ($ans['id'] == $correct) ){
			$is_correct	=	1;
		}
	}
	
	return $is_correct;
}

function yaexam_is_multiple_answers_correct($answers = array(), $corrects = array()) {
	
	if( !$corrects || !is_array($corrects) || !is_array($answers) || !$answers ){
		return 0;
	}

	
	$num_corrects	=	count(array_filter( $answers, function( $value ){

		return $value['is_correct'] == 1;
	}));

	if($num_corrects != count($corrects)){

		return 0;
	}
	
	$is_correct = 1;

	foreach($answers as $id => $ans){
		if(in_array($ans['id'], $corrects)){
			if( $ans['is_correct'] == -1 ) {
				$is_correct	=	0;
			}
		}
	}
	
	return $is_correct;
}

function yaexam_is_fill_blank_answers_correct($answers, $corrects = '') {
	
	if(is_array($answers) || is_array($corrects)) return 0;
	
	$is_correct = strtolower($answers) == strtolower($corrects) ? 1 : 0;
	
	return $is_correct;
	
}

function yaexam_is_drag_match_answers_correct( $answers, $corrects = array() ) {
	
	$is_correct = 1;
	
	if( !$answers || !$corrects ) return 0;
		
	ksort($corrects);
	
	foreach( $answers as $key => $ans ) {
		
		foreach( $corrects as $id => $value ) {
			
			if($ans['id'] == $id ) {
				
				if( $ans['value'] != $value ) {
					
					$is_correct	=	0;
				}
			}
			
		}
		
	}
	
	return $is_correct;
}

function yaexam_is_group_match_answers_correct( $answers, $corrects = array() ) {

	$is_correct = 1;

	if( $answers && $corrects ) {

		foreach ($answers as $answer) {
			
			foreach ($corrects as $group_name => $values) {
				
				if( $values != '' ){
					if( sanitize_title( $answer['group'] ) == $group_name ) {

						if( $values ) {

							$values = explode(',', $values);

							if( $values ) {
								if( !in_array( $answer['id'], $values ) ) {

									$is_correct = 0;
								}
							}else{

								$is_correct = 0;
							}
						}
					}
				}else{

					$is_correct = 0;
				}

			}
		}

	}else{

		$is_correct = 0;
	}

	return $is_correct;
}

function yaexam_is_order_answers_correct( $answers, $corrects = array() ) {
	
	$is_correct = 1;
	
	if( !$answers || !$corrects ) return 0;
	
	$answers = array_values($answers);

	foreach( $answers as $key => $ans ) {
		
		if( absint($ans['id']) != absint($corrects[$key]) ){
			$is_correct = 0;
		}
	}
	
	return $is_correct;
}

function yaexam_is_guess_word_answers_correct($answers, $corrects = '') {
	
	if(!is_array($answers) || !is_array($corrects) || !$answers || !$corrects) return 0;

	$str_answers  = array();
	$str_corrects = array();

	foreach( $answers as $ans ) {

		$str_answers[] = strtolower( $ans['content'] );
	}

	array_walk( $corrects, function( &$value ){
		
		$value = strtolower( $value );
	});

		
	$result = array_diff( $str_answers, $corrects );
	
	if( empty( $result ) ) {

		return 1;

	}else{

		return 0;
	}
}

function yaexam_is_keywords_answers_correct($answers, $corrects = '', $params = array()) {
	
	if(is_array($corrects) || !$corrects) return 0;
	
	$num_corrects = 0;

	foreach( $answers as $word ) {
		
		if (strpos(strtolower($corrects), strtolower($word['content'])) !== false) {
			
		    $num_corrects++;
		}
	}
	
	if( $num_corrects >= $params['min_corrects'] ) {

		return 1;
	}

	return 0;
}

function yaexam_question_get_answer_type( $type = '' ) {
	
	$types	=	 apply_filters( 'yaexam_question_answer_types', array(
			'single'		=> __( 'Single choice', 'yaexam' ),
			'multiple'		=> __( 'Multiple choices', 'yaexam' ),
			'fill_blank'	=> __( 'Fill in the blank', 'yaexam' ),
			'drag_match'	=> __( 'Item match', 'yaexam' ),
			'group_match'	=> __( 'Group match', 'yaexam' ),
			'order'			=> __( 'Order', 'yaexam'),
			'guess_word'	=> __( 'Guess Word', 'yaexam'),
			'keywords'		=> __( 'Keywords', 'yaexam'),
		));
	
	if($type){
		
		return $types[$type];
		
	}else{
		
		return $types;
	}
}

function yaexam_question_is_instanted_answer( $question_id, $instant_answer ) {

	return false;
}

function yaexam_question_is_correct( $question_1, $value ) {

	$is_correct = false;

	$type = $question_1['answer_type'];
	
	switch( $type ){
		case 'single':
		
			$is_correct = yaexam_is_single_answers_correct( $question_1['answers'], $value );
			
			break;
		case 'multiple':
		
			$is_correct = yaexam_is_multiple_answers_correct( $question_1['answers'], $value );
			
			break;
	}

	return apply_filters( 'yaexam_question_is_correct', $is_correct, [$question_1, $value] );
}

function yaexam_questions_get_results( $questions ) {

	if( !isset($questions) || !$questions ) { return array(); }

	$result = array( 'score' => 0, 'total' => 0, 'num_corrects' => 0, 'questions' => array() );

	foreach( $questions as &$question ) {

		$correct_answers = yaexam_get_post_meta( $question['id'], 'answers' );

		$question['is_correct'] =	yaexam_question_is_correct( $correct_answers, $question['value'], $question['type'] );
		$question['answer']		=	$correct_answers;

		$score = absint(yaexam_get_post_meta( $question['id'], 'score' ));

		$result['total'] += $score;

		if( $question['is_correct'] ) {

			$result['score'] += $score;

			$result['num_corrects']++;
		}
	}

	$result['percent']	=	round(($result['num_corrects'] * 100) / count( $questions ), 1);

	$result['questions'] = $questions;

	return $result;
}

function yaexam_questions_group_match_get_groups( $answers ) {

	$groups = array();

	if( $answers ) {

		foreach( $answers as $answer ) {

			if( !in_array( $answer['group'], $groups ) ) {

				$groups[sanitize_title($answer['group'])] = $answer['group'];
			}
		}
	}

	return $groups;
}

function yaexam_questions_group_match_is_play( $answers ) {

	$is_play = false;

	if( $answers ){

		foreach( $answers as $answer ) {

			if( $answer != '' ) {

				$is_play = true;
			}
		}
	}

	return $is_play;
}