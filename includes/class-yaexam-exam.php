<?php 

namespace YaExam;

defined( 'ABSPATH' ) || exit;
class YAEXAM_Exam {
	
	public $id = 0;
	
	public $post = null;
	
	public function __construct( $exam ) {
		if ( is_numeric( $exam ) ) {
			$this->id   = absint( $exam );
			$this->post = get_post( $this->id );
		} elseif ( $exam instanceof EM_Exam ) {
			$this->id   = absint( $exam->id );
			$this->post = $exam->post;
		} elseif ( isset( $exam->ID ) ) {
			$this->id   = absint( $exam->ID );
			$this->post = $exam;
		}
		
		$this->settings	=	$this->get_settings();
	}
	
	public function __isset( $key ) {
		return metadata_exists( 'post', $this->id, '_' . $key );
	}
	
	public function __get( $key ) {
		$value = get_post_meta( $this->id, '_' . $key, true );
		
		if ( false !== $value ) {
			$this->$key = $value;
		}

		return $value;
	}
	
	public function get_post_data() {
		return $this->post;
	}
	
	public function get_id() {
		return $this->id;
	}
	
	public function get_permalink() {
		return esc_url( get_permalink( $this->id ) );
	}

	public function get_result_permalink( $result_id ) {

		if( !$result_id ) {

			$result_id = 0;
		}

		return add_query_arg( 'result', $result_id, get_permalink( $this->id ) );
	}

	public function get_html_permalink() {

		return '<a href="' . $this->get_permalink() . '">' . $this->get_title() . '</a>';
	}
	
	public function get_category( $formated = true ) {
		if( $formated ) {
			
			return get_the_category_list(' ', 'single', $this->id);
		}
	}

	public function get_type(){

		$type_examing	=	yaexam_get_post_meta( $this->id, 'type_examing' );

		if( !$type_examing ) {

			return 0;
		}

		return $type_examing;
	}
	
	public function get_image_id() {

		if ( has_post_thumbnail( $this->id ) ) {
			$image_id = get_post_thumbnail_id( $this->id );
		} elseif ( ( $parent_id = wp_get_post_parent_id( $this->id ) ) && has_post_thumbnail( $parent_id ) ) {
			$image_id = get_post_thumbnail_id( $parent_id );
		} else {
			$image_id = 0;
		}

		return $image_id;
	}
	
	public function get_author_id() {
		
		return $this->post->post_author;
	}
	
	public function get_title() {

		$title = $this->post->post_title;

		return apply_filters( 'yaexam_exam_title', $title, $this );
	}
	
	public function get_duration( $is_formated = true ) {
		
		if( $is_formated ) {
			$duration	=	$this->duration ? $this->duration . ' ' . __('minutes', 'yaexam') : __('unlimited', 'yaexam');
		}else{
			
			$duration = $this->duration;
		}
		
		return $duration;
	}
	
	public function get_attempt( $is_formated = true ) {
		
		if($is_formated){
			$attempt = $this->attempt ? $this->attempt : __('unlimited', 'yaexam');
		}else{
			
			$attempt = $this->attempt;
		}
		
		return $attempt;
	}
	
	public function get_memberships() {
		
		$memberships	=	yaexam_get_post_meta( $this->id, 'memberships' );
		
		if( isset($memberships) && $memberships ) {
			
			return yaexam_get_memberships(array('include' => $memberships));
		}
		
		return false;
	}
	
	public function get_played(){
				
		return $this->played ? $this->played : 0;
	}

	public function get_image( $size = 'thumbnail', $attr = array() ) {
		if ( has_post_thumbnail( $this->id ) ) {
			$image = get_the_post_thumbnail( $this->id, $size, $attr );
		} elseif ( ( $parent_id = wp_get_post_parent_id( $this->id ) ) && has_post_thumbnail( $parent_id ) ) {
			$image = get_the_post_thumbnail( $parent_id, $size, $attr );
		}

		return $image;
	}
	
	public function get_publish() {
		
		return yaexam_get_post_meta( $this->id, 'publish_for' );
	}
	
	public function get_settings(){
		
		$settings	=	 yaexam_exam_get_settings( $this->id );
		
		return $settings;
	}

	public function get_setting( $name ){

		$settings	=	 yaexam_exam_get_settings( $this->id );
		
		return isset($settings[$name]) ? $settings[$name] : false;
	}
	
	public function set_settings( $key, $value ) {
			
		$settings	=	$this->exam_settings ? $this->exam_settings : array();
	
		$settings	=	wp_parse_args($settings, array(
			'display_perpage'	=>	10,
			'duration'			=>	20,
			'publish_for'		=>	0,
			'attempt'			=>	0,
			'assigned_users'	=>	array(),
			'memberships'		=>	array(),
			'played'			=>	0,
			'is_reviews'		=>	1,
			'is_email_result'	=>	1,
			'is_sidebar_tracking'	=>	1,
			'ranking'			=>	array(),
			'rating'			=>	array( 
										'1_star' => 0, 
										'2_star' => 0, 
										'3_star' => 0, 
										'4_star' => 0, 
										'5_star' => 0, 
										'final'	 => 0
									)
		));
	
		$settings[$key]	=	$value;
	
		return update_post_meta( $this->id, '_exam_settings', $settings );
		
	}
	
	public function get_date_created( $time_format = '' ){
		
		if(!$time_format) {
			$time_format	=	get_option('date_format');
		}
		
		return get_the_date( $time_format, $this->id );
	}
	
	public function get_comments_number(){
		
		return get_comments_number( $this->id );
	}
	
	public function get_questions2() {
		
		$questions			=	array();
		
		$fixed_questions	=	yaexam_question_get_fixed_items( $this->id );
		
		$random_questions	=	yaexam_question_get_random_doing_items( $this->id, yaexam_get_values_by_key($fixed_questions, 'id') );
		
		if($random_questions){
			
			if( $fixed_questions && $random_questions['position'] == 'bottom_fquestions' ){
				
				$questions	=	array_merge( $fixed_questions, $random_questions['questions'] );
				
			}elseif( $fixed_questions && $random_questions['position'] == 'top_fquestions') {
				
				$questions	=	array_merge( $random_questions['questions'], $fixed_questions );
				
			}else{
				
				if( $fixed_questions ){
					$questions	=	array_merge( $fixed_questions, $random_questions['questions'] );
				}else{
					$questions	=	$random_questions['questions'];
				}
					
				shuffle($questions);
			}
			
		} else {
			
			$questions	=	$fixed_questions;
		}

		if( $questions ) {

			foreach( $questions as $index => &$q ) {

				if( ($q['answer_type'] == 'single') || ($q['answer_type'] == 'multiple') ) {

					if( $q['answers'] ) {

						foreach( $q['answers'] as $ans_id => &$ans ) {

							$ans['id'] = $ans_id;
						}

						shuffle($q['answers']);
					}
				}
			}
		}
		
		return $questions;
	}

	public function get_questions() {

		$exam_questions = yaexam_get_exam_questions( $this->id );

		$unique_questions = yaexam_get_question_by_ids( $exam_questions );
		
		$questions = [];
		
		if( $exam_questions ) {
			foreach( $exam_questions as $r ) {
				$questions[] = $unique_questions['q_' . $r];
			}
		}

		return $questions;
	}
		
	public function get_doing_data() {
		
		$settings			=	yaexam_exam_get_settings( $this->id );
		
		$questions			=	$this->get_questions();
		
		if($questions){

			foreach( $questions as $index => &$q ) {

				$q['answers'] = json_decode( $q['answers'], true );
				$q['params']  = json_decode( $q['params'], true );

				if( ($q['answer_type'] == 'single') || ($q['answer_type'] == 'multiple') ) {

					if( $q['answers'] ) {

						foreach( $q['answers'] as $ans_id => &$ans ) {

							$ans['id'] = $ans_id;
						}

						shuffle($q['answers']);
					}
				}

				if( $settings['shuffle_answers'] == 'yes' ) {
					shuffle($q['answers']);
				}
			}

			if( $settings['shuffle_questions'] == 'yes' ) {
				shuffle($questions);
			}

			return array(
				'tid'			  =>	$this->id,
				'questions'		  =>	$questions,
				'settings'		  =>	$settings
			);
		}
		
		return array();
	}

	public function filter_adaptive_exclude_questions( $q_content, $q_id ) {

		foreach( $this->adaptive_questions as $key => &$a_q ) {
			if( $a_q['id'] == $q_id ) {

				if( $q_content['is_correct'] == 1 ) {
					
					++$this->adaptive_questions[$key]['num_correct'];
					
				}

				return $a_q['num_correct'] >= $this->settings['adaptive_times'];
			}
		}
	}

	public function get_doing_adaptive_data( $questions = array(), $questions_result = array() ){

		if( !$questions ){
			return array();
		}

		$session    				= 	new EM_Exam_Session();
		
		$session_data				=	$session->get('doing');

		$settings					=	yaexam_exam_get_settings( $this->id );

		$this->adaptive_questions	=	$session_data['adaptive_questions'];

		if( !$this->adaptive_questions ) {

			$this->adaptive_questions	=	array();

			foreach( $session_data['ids'] as $id ) {
				array_push( $this->adaptive_questions, array('id' => $id, 'num_correct' => 0) );
			}
		}

		if( $questions_result ) {
			$exclude_questions	=	array_filter( $questions_result, array( $this, 'filter_adaptive_exclude_questions' ), ARRAY_FILTER_USE_BOTH);
		}
		
		// Correct not 100%
		if( $exclude_questions ){
			$exclude_ids		=	array_keys($exclude_questions);

			foreach( $questions as $key => $q ) {

				if( $exclude_ids && in_array( $q->ID, $exclude_ids) ) {
					unset($questions[$key]);
				}
			}
		}

		// Percent correct questions
		$num_corrects	=	0;
		$ids_corrects	=	array();

		foreach( $this->adaptive_questions as $key => $ad_q ) {

			if( $ad_q['num_correct'] == $settings['adaptive_times'] ){

				array_push( $ids_corrects, $ad_q['id'] );
				$num_corrects++;
			}

		}

		$percent_correct	=	($num_corrects * 100) / count($this->adaptive_questions);
		
		$perpage		=	$settings['display_perpage'];

		// Loading questions
		if( $questions ) {
		
			shuffle( $questions );
				
			$ids			=	yaexam_get_values_by_key($questions, 'ID');
			
			$perpage		=	!$settings['display_perpage'] ? count($ids) : $settings['display_perpage'];
			
			$total_page		=	ceil(count($ids) / $perpage);
			
			$pages_question	=	array_chunk($ids, $perpage);
			
			return array(
				'tid'					=>	$this->id,
				'questions'				=>	$questions,
				'settings'				=>	$settings,
				'total_page' 			=>	$total_page,
				'perpage'				=>	$perpage,
				'page_question'			=>	$pages_question,
				'ids_question'			=>	$ids,
				'adaptive_questions'	=>	$this->adaptive_questions,
				'adaptive_percent'		=>	$percent_correct,
				'adaptive_num_corrects'	=>	$num_corrects,
				'adaptive_ids_corrects'	=>	$ids_corrects
			);

		}else{

			return array(
				'tid'					=>	$this->id,
				'questions'				=>	array(),
				'settings'				=>	$settings,
				'total_page' 			=>	0,
				'perpage'				=>	$perpage,
				'page_question'			=>	array(),
				'ids_question'			=>	array(),
				'adaptive_questions'	=>	$this->adaptive_questions,
				'adaptive_percent'		=>	$percent_correct,
				'adaptive_num_corrects'	=>	$num_corrects,
				'adaptive_ids_corrects'	=>	$ids_corrects
			);
		}
	}

	public function get_doing_infinite_data( $questions = array(), $questions_result = array() ){

		if( !$questions ){
			return array();
		}
		
		$session    	= 	new EM_Exam_Session();

		$session_data	=	$session->get('doing');

		$settings		=	yaexam_exam_get_settings( $this->id );

		$settings['adaptive_max_round'] = 0;
		$settings['adaptive_times'] 	= 1;

		$this->settings = $settings;

		$this->adaptive_questions	=	$session_data['adaptive_questions'];

		if( !$this->adaptive_questions ) {

			$this->adaptive_questions	=	array();

			foreach( $session_data['ids'] as $id ) {
				array_push( $this->adaptive_questions, array('id' => $id, 'num_correct' => 0) );
			}
		}

		if( $questions_result ) {
			$exclude_questions	=	array_filter( $questions_result, array( $this, 'filter_adaptive_exclude_questions' ), ARRAY_FILTER_USE_BOTH);
		}
		
		// Correct not 100%
		if( $exclude_questions ){
			$exclude_ids		=	array_keys($exclude_questions);

			foreach( $questions as $key => $q ) {

				if( $exclude_ids && in_array( $q->ID, $exclude_ids) ) {
					unset($questions[$key]);
				}
			}
		}

		// Percent correct questions
		$num_corrects	=	0;
		$ids_corrects	=	array();

		foreach( $this->adaptive_questions as $key => $ad_q ) {

			if( $ad_q['num_correct'] == $settings['adaptive_times'] ){

				array_push( $ids_corrects, $ad_q['id'] );
				$num_corrects++;
			}

		}

		$percent_correct	=	($num_corrects * 100) / count($this->adaptive_questions);
		
		$perpage		=	$settings['display_perpage'];

		// Loading questions
		if( $questions ) {
		
			shuffle( $questions );
				
			$ids			=	yaexam_get_values_by_key($questions, 'ID');
			
			$perpage		=	!$settings['display_perpage'] ? count($ids) : $settings['display_perpage'];
			
			$total_page		=	ceil(count($ids) / $perpage);
			
			$pages_question	=	array_chunk($ids, $perpage);
			
			return array(
				'tid'					=>	$this->id,
				'questions'				=>	$questions,
				'settings'				=>	$settings,
				'total_page' 			=>	$total_page,
				'perpage'				=>	$perpage,
				'page_question'			=>	$pages_question,
				'ids_question'			=>	$ids,
				'adaptive_questions'	=>	$this->adaptive_questions,
				'adaptive_percent'		=>	$percent_correct,
				'adaptive_num_corrects'	=>	$num_corrects,
				'adaptive_ids_corrects'	=>	$ids_corrects
			);

		}else{

			return array(
				'tid'					=>	$this->id,
				'questions'				=>	array(),
				'settings'				=>	$settings,
				'total_page' 			=>	0,
				'perpage'				=>	$perpage,
				'page_question'			=>	array(),
				'ids_question'			=>	array(),
				'adaptive_questions'	=>	$this->adaptive_questions,
				'adaptive_percent'		=>	$percent_correct,
				'adaptive_num_corrects'	=>	$num_corrects,
				'adaptive_ids_corrects'	=>	$ids_corrects
			);
		}
	}
	
	public function get_result_data( $result_id, $from_session = false ) {
			
		$show_answers	=	$this->settings['result_is_answers'];
		
		$result	=	yaexam_get_result($result_id);
		
		if($result)
		{

			$user_answers   =	$result['answers'];
			$user_others   =	$result['others'];

			if( is_string($result['answers']) ) {

				$user_answers	=	$result['answers'] ? json_decode($result['answers'], true) : array();
			}

			if( is_string($result['others']) ) {

				$user_others 	=	$result['others'] ? json_decode($result['others'], true) : array();
			}

			$num_corrects 	=	0;

			if(isset($user_others))
			{
				$question_params = $user_others;
			}else{
				$question_params = array();
			}
			
			if( isset($user_others['questions']) )
			{

				$question_ids 		=	yaexam_get_values_by_key( $user_others['questions'], 'id' );

				$questions			=	yaexam_get_doing_questions( array( 'ids' => $question_ids ) );


			}else{

				$questions 			=	yaexam_question_set_answers($this->get_questions());

			}

			if($user_answers){
				
				foreach( $questions as &$q ){
					
					foreach($user_answers as $qid => &$uq){
						
						if($qid == $q->ID){
							
							switch($q->answer_type){
								
								case 'single':
									$answers	=	$q->answers;
													
									$q->is_correct	=	$uq['is_correct'];

									foreach($answers as $aid => &$ans){
										
										if(isset($uq['answers']) && $aid == $uq['answers']){
											
											if(isset($uq['answers'][$aid]) && is_array($uq['answers'][$aid]))
												$uq['answers'][$aid]['user_check'] = 1;
											
											
											if($q->answers[$aid]['is_correct'] == 1){
											
												$q->is_correct = 1;
											}else{
												$q->is_correct = 0;
											}
											
										}
										
										if(!$show_answers){
											$q->answers[$aid]['is_correct']	=	0;
										}
									}

									if(isset($uq['answers']) && is_array($uq['answers']) ){
										$q->answers = $uq['answers'];
									}
									
								break;
								
								case 'multiple':
									$answers	=	$q->answers;
									
									$q->is_correct	=	$uq['is_correct'];
									
									foreach($answers as $aid => &$ans){
										
										if(isset($uq['answers']) && in_array($aid, $uq['answers'])){
											
											if(isset($uq['answers'][$aid]) && is_array($uq['answers'][$aid]))
												$uq['answers'][$aid]['user_check'] = 1;
											
										}
										
										if(!$show_answers){
											$q->answers[$aid]['is_correct'] = 0;
										}
									}

									if(isset($uq['answers']) && is_array($uq['answers']) ){
										$q->answers = $uq['answers'];
									}
							
								break;
								
								case 'fill_blank':
									
									$q->is_correct	=	$uq['is_correct'];

									if(!$show_answers){
										
										$q->answers = '';

									}
									
									if( isset($uq['answers']) ){

										$q->u_answers = $uq['answers'];

									}else{

										$q->u_answers = '';
									}

								break;
								
								case 'drag_match':
									
									$q->is_correct	=	$uq['is_correct'];
									$q->u_answers	=	array();

									if(isset($uq['answers']) && is_array($uq['answers']) ){
										$q->answers = $uq['answers'];
									}
									
								break;

								case 'group_match':

									$q->is_correct	=	$uq['is_correct'];
									
									$q->user_answer = $uq['answers'];

								break;
								
								case 'order':
									
									$q->is_correct	=	$uq['is_correct'];
									$q->u_answers	=	array();

									if(isset($uq['answers']) && is_array($uq['answers']) ){
										$q->answers = $uq['answers'];
									}
									
								break;

								case 'guess_word':
									
									$q->is_correct	=	$uq['is_correct'];
									
									if(!$show_answers){
										
										$q->answers = $uq['answers'];

									}else{

										$raw = array();
										
										foreach($q->answers as $ans) {

											$raw[] = $ans['content'];
										}

										$q->answers = $raw;
									}

								break;
								
								case 'keywords':
									
									$q->is_correct	=	$uq['is_correct'];
									
									$q->user_answer = $uq['answers'];

									if(!$show_answers){
										
										$q->answers = '';

									}

									if(isset($uq['answers']) && is_array($uq['answers']) ){
										$q->answers = $uq['answers'];
									}

								break;
							}

							$q->score = apply_filters( 'yaexam_exam_get_result_loop_score_data', $q->score, $q, $uq );
														
						}
					}
					
					if( $q->is_correct ) {

						$num_corrects++;
					}


				}
			}

			$total 				=	count($questions);
			
			$ranking 			=	yaexam_is_ranking( $this->id, $result['score'], $result['total_score'], false );
			
			$perpage			=	!$this->settings['display_perpage'] ? $total : $this->settings['display_perpage'];
			
			$total_page			=	ceil($total / $perpage);
						
			$page_question		=	array_chunk($questions, $perpage);
			
			//Set order index
			foreach( $page_question as $page => $pq ) {
				
				$from_order	=	($page * $this->settings['display_perpage']) + 1;
				
				foreach( $pq as &$pquestion ){
				
					$pquestion->order	=	$from_order++;
						
				}
				
			}

			return array(
				'tid'			  =>	$this->id,
				'questions'		  =>	$questions,
				'question_params' => 	$question_params,
				'settings'		  =>	$this->settings,
				'total_questions' =>	$total,
				'total_page' 	  =>	$total_page,
				'perpage'		  =>	$perpage,
				'page_question'	  =>	$page_question,
				'ranking' 		  =>	$ranking,
				'result'		  =>	$result,
				'num_corrects'	  => 	$num_corrects
			);
		}
	}
	
	public static function get_result( $result_id, $is_include_data = true ) {
		global $wpdb;
		
		$result_tbl	=	$wpdb->prefix . 'yaexam_results';
		
		$result = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $result_tbl . ' WHERE id = %d', $result_id), ARRAY_A);
		
		return $result;
	}

	public function get_session_result() {
		
		$session    = 	new EM_Exam_Session();
		
		$doing_data = $session->get('doing');
		
		$result = $session->get('result_' . $this->id);
		
		$result['questions'] = $doing_data['questions'];
		
		return $result;
	}

	public static function store_results( $result = array(), $exam_id ) {

		if( !$result || !$exam_id ){
			return false;
		}

		$result['exam_id']		=	absInt( $exam_id );
		$result['user_id']		=	0;

		$current_user = wp_get_current_user();

		if ( ($current_user instanceof WP_User) && $current_user->ID > 0 ) {

			$result['user_id']	  =	$current_user->ID;
			
			$result['user_email'] = $current_user->user_email;

			$result['user_name']  = yaexam_get_formated_user_name( $current_user );
		}
				
		if( $result_answers = $result['answers'] ) {
			
			$result_answers = yaexam_object_to_array( $result_answers );
							
			$result['answers']	=	json_encode( $result_answers );
		}

		if( isset($result['others']) )
		{
			$result['others']	=	json_encode( $result['others'] );
		}
		
		$table_result	=	new EM_Table_Result();
		
		$table_result->save($result);
		
		return $table_result->getId();
	}
	
	public function get_certificate( $result, $force_download = true ) {
						
		$settings	=	$this->get_settings();
		
		if(!isset($result['certificate_id']) || !$result['certificate_id']) {
			
			return false;
		} 
		
		$certid = get_post_meta( $result['certificate_id'], 'certid', true );

		if( !$certid ) {

			$certid = substr(uniqid(), 0, 10);

			update_post_meta( $result['certificate_id'], 'certid', $certid );
		}
		
		$background_id	=	yaexam_get_post_meta( $result['certificate_id'], 'certificate_background' );
		$data			=	yaexam_get_post_meta( $result['certificate_id'], 'certificate_data');
		
		$background_src	=	wp_get_attachment_image_src( $background_id, 'full' );
		
		$certificate	=	new EM_Image( array('src' => $background_src[0]) );
		
		if($data && is_array($data)){

			foreach( $data as $d ) {

				switch( $d['type'] ){
					case 'exam_name':
						
						$certificate->addText( array(
							'text' 			=> $this->get_title(),
							'font_name' 	=> $d['font_name'],
							'font_size'		=> $d['font_size'],
							'text_align'	=> $d['text_align'],
							'color'			=> $d['color'],
							'angle'			=>	0,
							'x'				=> $d['x'],
							'y'				=> $d['y']
						 ) );
						
					break;
					case 'name':

						if( isset($result['user_name']) && $result['user_name'] ) {

							$name = $result['user_name'];
				
							$certificate->addText( array(
								'text' 			=> $name,
								'font_name' 	=> $d['font_name'],
								'font_size'		=> $d['font_size'],
								'text_align'	=> $d['text_align'],
								'color'			=> $d['color'],
								'angle'			=>	0,
								'x'				=> $d['x'],
								'y'				=> $d['y']
							) );

						}
						 
					break;
					case 'date':
						
						$date	=	yaexam_get_date_formated($result['date_start'], yaexam_date_format());
						
						$certificate->addText( array(
							'text' 			=> $date, 
							'font_name' 	=> $d['font_name'],
							'font_size'		=> $d['font_size'],
							'text_align'	=> $d['text_align'],
							'color'			=> $d['color'],
							'angle'			=>	0,
							'x'				=> $d['x'],
							'y'				=> $d['y']
						 ) );
						
					break;
					case 'rank':
						
						$certificate->addText( array(
							'text' 			=>	$result['ranking'], 
							'font_name' 	=> $d['font_name'],
							'font_size'		=> $d['font_size'],
							'text_align'	=> $d['text_align'],
							'color'			=> $d['color'],
							'angle'			=>	0,
							'x'				=> $d['x'],
							'y'				=> $d['y']
						 ) );
						
					break;
					case 'score':
						
						$certificate->addText( array(
							'text' 			=>	$result['score'], 
							'font_name' 	=> $d['font_name'],
							'font_size'		=> $d['font_size'],
							'text_align'	=> $d['text_align'],
							'color'			=> $d['color'],
							'angle'			=>	0,
							'x'				=> $d['x'],
							'y'				=> $d['y']
						 ) );
						
					break;
					case 'id':
						
						$certificate->addText( array(
							'text' 			=> strtoupper($certid), 
							'font_name' 	=> $d['font_name'],
							'font_size'		=> $d['font_size'],
							'text_align'	=> $d['text_align'],
							'color'			=> $d['color'],
							'angle'			=>	0,
							'x'				=> $d['x'],
							'y'				=> $d['y']
						 ) );
						
					break;
					case 'random_cert_id':
						
						$certificate->addText( array(
							'text' 			=> $result['cert_id'], 
							'font_name' 	=> $d['font_name'],
							'font_size'		=> $d['font_size'],
							'text_align'	=> $d['text_align'],
							'color'			=> $d['color'],
							'angle'			=>	0,
							'x'				=> $d['x'],
							'y'				=> $d['y']
						 ) );
						
					break;
					case 'text':
						
						$certificate->addText( array(
							'text' 			=>	$d['content'], 
							'font_name' 	=> $d['font_name'],
							'font_size' 	=> $d['font_size'],
							'text_align'	=> $d['text_align'],
							'color'			=> $d['color'],
							'angle'			=>	0,
							'x'				=> $d['x'],
							'y'				=> $d['y']
						 ) );
						
					break;
					case 'usermeta':

						$usermeta_value = $result['user_meta'];

						if( $usermeta_value && is_string($usermeta_value) ){

							$usermeta_value = json_decode( $result['user_meta'], true );
							
							if( is_array($usermeta_value) ){

								$label = isset($usermeta_value[ trim($d['content'], '[,]') ]) ? $usermeta_value[ trim($d['content'], '[,]') ] : false;

								$um = isset($usermeta_value[trim($d['content'], '[,]')]) ? $usermeta_value[trim($d['content'], '[,]')] : '';
								
								$certificate->addText( array(
									'text' 			=> $um, 
									'font_name' 	=> $d['font_name'],
									'font_size' 	=> $d['font_size'],
									'text_align'	=> $d['text_align'],
									'color'			=> $d['color'],
									'angle'			=>	0,
									'x'				=> $d['x'],
									'y'				=> $d['y']
								 ) );
							
							}
						}

					break;
				}
			}
		}
		
		if( $force_download ) {

			$certificate->output( $result['cert_id'] );

		}else{

			return $certificate->store( $result['cert_id'], false );
		}
	}

	public function get_rating( $user_id = false ) {
		
		$rating = get_post_meta( $this->id, '_rating', true );

		if( !$rating ){

			$rating = array( 
							'users'	 =>	array(),
							'stars'	 =>	array(
								1 => 0,
								2 => 0,
								3 => 0,
								4 => 0,
								5 => 0,
							),
							'star'	 => 0
						);
		}

		if( $user_id ) {

			if( $rating['users'] ){

				foreach( $rating['users'] as $user ) {

					if( $user['id'] == $user_id ) {

						return $user['star'];
					}
				}
			}

			return 0;
		}

		return $rating;
	}

	public function set_rating( $user_id, $star ) {

		$rating = $this->get_rating();

		if( !isset($rating['stars']) ) {

			$rating['stars'] = array(
								1 => 0,
								2 => 0,
								3 => 0,
								4 => 0,
								5 => 0,
							);
		}

		$is_rated = false;

		if( $rating['users'] ) {

			foreach( $rating['users'] as &$user ) {

				if( $user['id'] == $user_id ) {

					$is_rated = true;

					if( $user['star'] != $star ) {

						if( $rating['stars'][$user['star']] > 0 ){
							$rating['stars'][$user['star']]--;
						}

						$rating['stars'][$star]++;
					}

					$user['star'] = $star;

				}
			}
		}

		if( !$is_rated ) {

			$rating['stars'][$star]++;

			$rating['users'][] = array( 'id' => $user_id, 'star' => $star );
			
		}

		$max = 0;
		$n = 0;

		foreach ($rating['stars'] as $rate => $count) {
		    $max += $rate * $count;
		    $n += $count;
		}

		$rating['star'] = round($max / $n);
		
		update_post_meta( $this->id, '_rating', $rating );
	}

	public function reset_rating() {

		$rating = array( 
			'users'	 =>	array(),
			'stars'	 =>	array(
				1 => 0,
				2 => 0,
				3 => 0,
				4 => 0,
				5 => 0,
			),
			'star'	 => 0
		);

		update_post_meta( $this->id, '_rating', $rating );
	}

	public function html_rating( $echo = true ) {

		$rating = $this->get_rating();

		$html = aws_rating_html( $rating['star'], $rating['users'] );

		if( $echo ) {

			echo $html;

		}else{

			return $html;
		}
	}

	public function get_fixed_questions( $params = array() ) {

		$params 	=	wp_parse_args( $params, array( 'perpage' => 20, 'page' => 1, 'exclude' => array() ) );

		$fixed_question_ids 	=	get_post_meta( $this->id, '_fixed_questions', true);

		$response 	= 	array(
			'data' 		 => array(), 
			'pagination' => array( 'total' => 0, 'pages' => 0, 'perpage' => $params['perpage'], 'page' => 1));

		if( $fixed_question_ids ){

			$total 		=	count( $fixed_question_ids );
			$page 		=	absInt( $params['page'] );
			$perpage	=	$params['perpage'];
			$pages 		= 	ceil( $total/$perpage );

			if($page > $pages){
				$page = 1;
			}

			$page_data  = $fixed_question_ids;

			if($perpage > 0){
				$pages_data = array_chunk( $fixed_question_ids, $perpage );
				$page_data  = $pages_data[$page - 1];
			}

			$params =  array(
			 	'posts_per_page'	=>	-1,
			 	'offset'			=>	0,
				'post_type'			=>	'question',
				'orderby'   		=>	'post__in',
				'include'			=>	$page_data,
				'suppress_filters'	=>	false
			 );

			$questions = get_posts($params);

			$response['data'] 	= $questions;
			$response['pagination']['total'] 	= $total;
			$response['pagination']['pages'] 	= $pages;
			$response['pagination']['page'] 	= $page;
		}

		return $response;
	}

	public function add_fixed_questions( $ids ) {

		if( !$ids ){ return false; }

		$ids_1 	=	yaexam_get_post_meta( $this->id, 'fixed_questions' );

		$ids_1		=	$ids_1 ? $ids_1 : array();
		$response	=	array();

		$fixed_questions = array_unique(array_merge( $ids, $ids_1 ));
		
		yaexam_update_post_meta( $this->id, array( 'fixed_questions' => $fixed_questions ) );
		
		return $fixed_questions;
	}

	public function remove_fixed_questions( $ids ) {

		$ids_1 	=	yaexam_get_post_meta( $this->id, 'fixed_questions' );
		
		$ids_1	=	$ids_1 ? $ids_1 : array();
		
		$fixed_questions = array();

		if($ids_1){

			$fixed_questions	=	array_diff($ids_1, $ids);
		}
		
		return yaexam_update_post_meta( $this->id, array('fixed_questions' => $fixed_questions) );
	}

	public function order_fixed_questions( $order_ids ) {

		if( !$order_ids ){ return false; }

		$ids 	=	yaexam_get_post_meta( $this->id, 'fixed_questions' );
		
		$ids	=	$ids ? $ids : array();

		if( !$ids ){ return false; }

		yaexam_update_post_meta( $this->id, array( 'fixed_questions' => $order_ids ) );

		return true;
	}

	public function get_assigned_users( $params = array() ) {

		$user_ids	=	$this->get_setting( 'assigned_users' );
		
		$response 	= 	array(
			'data' 		 => array(), 
			'pagination' => array( 'total' => 0, 'pages' => 0, 'perpage' => $params['perpage'], 'page' => 1));

		if( $user_ids ) { 

			$params 	=	wp_parse_args( $params, array( 'perpage' => 3, 'page' => 1 ) );

			$page 		=	absInt($params['page']);

			$total 		=	$this->get_total_assigned_users();

			$pages  	= 	ceil( $total/ $params['perpage'] );

			if($page > $pages){
				$page = 1;
			}
			
			$user_query = 	new WP_User_Query( array( 
				'include' => $user_ids, 
				'number'  => $params['perpage'], 
				'paged'   => $page 
			) );

			$users 	=	$user_query->results;

			$response['data'] 	= $users;
			$response['pagination']['total'] 	= $total;
			$response['pagination']['pages'] 	= $pages;
			$response['pagination']['page'] 	= $page;
		}

		return $response;
	}

	public function get_total_assigned_users() {

		$user_ids	=	$this->get_setting('assigned_users');

		if( !$user_ids ) { return 0; }

		$user_query = 	new WP_User_Query( array( 
			'include' => $user_ids, 
			'number'  => -1
		) );

		return count( $user_query->results );
	}
	
	public function get_results(){
		global $wpdb;
		
		$result_tbl	=	$wpdb->prefix . 'yaexam_results';
		
		$results = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $result_tbl . ' WHERE exam_id = %d', $this->id), ARRAY_A);
		
		return $results;
	}

	public function get_lasexam_results( $params = array() )
	{
		$results = $this->get_lasexam_pagination_results( $params );

		if(isset($results['data']))
		{
			return $results['data'];
		}

		return array();
	}
	
	public function get_lasexam_pagination_results( $params = array() ){
		
		global $wpdb;

		$params = wp_parse_args( $params, array(
			'perpage' 	=> 20, 
			'page' 		=> 1, 
			'order' 	=> 'r.date_start DESC',
			'user_meta' => false
		) );
		
		$result_tbl	=	$wpdb->prefix . 'yaexam_results';
		$user_tbl	=	$wpdb->prefix . 'users';

		$page 		=	absInt($params['page']);
		$perpage	=	absInt($params['perpage']);

		$total 		= 	$this->get_total_lasexam_results();
		
		$pages 		=	ceil($total / $perpage);
		$offset 	=	($page - 1) * $perpage;

		if($page > $pages){
			$page = 1;
		}

		$results = array();
		
		if( $total > 0 ){
			$query	=	$wpdb->prepare(
				'SELECT r.id, r.exam_id, r.user_id, r.score, r.total_score, r.percent, r.duration, r.date_start, r.date_end, u.user_nicename, u.display_name, u.user_email AS login_email, r.answers, r.others, r.user_email, r.user_name, r.user_meta '.
					'FROM ' . $result_tbl . ' r LEFT JOIN ' . $user_tbl . ' u ON u.id = r.user_id '.
					'WHERE r.exam_id = %d AND r.date_start IN(SELECT max(dr.date_start) FROM ' . $result_tbl . ' dr WHERE dr.exam_id = %d GROUP BY dr.user_email) '.
					'GROUP BY r.user_email ORDER BY ' . $params['order'] . ' LIMIT %d, %d'
				, $this->id, $this->id, $offset, $perpage);
			
			$results	= $wpdb->get_results($query, ARRAY_A);

			if($results){
			
				$results	=	yaexam_format_results( $results );	

				if( isset($params['user_meta']) && $params['user_meta'] ) {

					foreach( $results as &$r ) {

						if( isset($r['user_meta']) && $r['user_meta'] ){

							$user_meta = json_decode( $r['user_meta'], true );

							if( is_array($user_meta) ) {

								$r['user_meta'] = $user_meta;
							}
						}


					} 
				}
			}
		}

		return array( 
			'data' 		 =>  $results,
			'pagination' =>  array(
				'total' 	=> $total, 
				'pages' 	=> $pages, 
				'perpage' 	=> $perpage, 
				'page' 		=> $page
			) );
	}

	public function get_total_lasexam_results()
	{
		global $wpdb;

		$result_tbl	=	$wpdb->prefix . 'yaexam_results';
		$user_tbl	=	$wpdb->prefix . 'users';

		$query  = $wpdb->prepare(
			'SELECT COUNT(*) FROM ' . $result_tbl . ' r LEFT JOIN ' . $user_tbl . ' u ON u.id = r.user_id '.
				'WHERE r.exam_id = %d AND r.date_start IN(SELECT max(dr.date_start) FROM ' . $result_tbl . ' dr WHERE dr.exam_id = %d GROUP BY dr.user_email) '.
				'GROUP BY r.user_email', $this->id, $this->id);

		$total = $wpdb->get_col($query);
		
		if($total){
			return count($total);
		}else{
			return 0;
		}
	}

	public function get_total_user_results( $user_id )
	{
		global $wpdb;
		
		$result_tbl	=	$wpdb->prefix . 'yaexam_results';

		$query	=	$wpdb->prepare(
			'SELECT r.id, r.exam_id, r.score, r.total_score, r.percent, r.duration, r.date_start, r.date_end '.
				'FROM ' . $result_tbl . ' r '.
				'WHERE r.exam_id = %d AND r.user_id = %d '.
				'ORDER BY r.date_start DESC'
			, $this->id, $user_id);
		
		$results	=	 $wpdb->get_results($query, ARRAY_A);
					
		return $results;
	}
	
	public function get_user_results( $user_id, $page = 1, $perpage	= 10 ) {
		
		global $wpdb;
		
		$result_tbl	=	$wpdb->prefix . 'yaexam_results';
		
		$query	=	$wpdb->prepare(
			'SELECT r.id, r.exam_id, r.ranking_id, r.ranking_code, r.score, r.total_score, r.percent, r.duration, r.date_start, r.date_end '.
				'FROM ' . $result_tbl . ' r '.
				'WHERE r.exam_id = %d AND r.user_id = %d '.
				'ORDER BY r.date_start DESC'
			, $this->id, $user_id);
		
		$results	=	 $wpdb->get_results($query, ARRAY_A);
				
		return yaexam_pagination_format( $results, $page, $perpage );
	}
	
	public function remove_user_results( $user_id ) {
		
		global $wpdb;
		
		$result_tbl	=	$wpdb->prefix . 'yaexam_results';
		
		$query	=	$wpdb->prepare('DELETE FROM ' . $result_tbl . ' WHERE user_id = %d', $user_id);
		
		return $wpdb->query($query);
	}
	
	public function remove_results() {
		
		global $wpdb;

		yaexam_exam_remove_user_score( $this->id );
		
		$result_tbl	=	$wpdb->prefix . 'yaexam_results';
		
		$query	=	$wpdb->prepare('DELETE FROM ' . $result_tbl . ' WHERE exam_id = %d', $this->id);
		
		return $wpdb->query($query);
	}
	
	public function assign_users( $ids = array() ) {
		
		if( !$ids ) return false;
		
		$users_response	=	array();
		$users_save		=	array();
		
		foreach($ids as $user_id){
			
			if(!$this->is_assigned_user( $user_id )){
				
					$user	=	new WP_User($user_id);
					
					$user_name	=	sprintf('<a href="%s">%s</a>', admin_url('user-edit.php?user_id=' . $user->ID), $user->user_nicename . ' - ' . $user->user_email);
							
					array_push($users_response, array(
						'name'	=>	$user_name,
						'id'	=>	$user_id
					));
					
					array_push( $users_save, $user_id );
					
					$user_exams	=	yaexam_get_user_meta( $user_id, 'exams' );
									
					if( is_array( $user_exams ) ){
						
						array_push( $user_exams, $this->id );

						$user_exams	=	array_unique( $user_exams );
						
					}else{
						
						$user_exams	=	array( $this->id );
					}
					
					yaexam_update_user_meta( $user_id, array( 'exams' => $user_exams ) );
				
			}
		}
		
		if($users_save){
				
			$settings	=	$this->get_settings();

			if(!is_array($settings['assigned_users'])){

				$settings['assigned_users'] = array();
			}
			
			$current_users	=	$settings['assigned_users'];

			if(!is_array($current_users))
			{
				$current_users = array();
			}

			foreach( $users_save as $u ){

				array_push($current_users, $u);
			}
			
			$users_save	=	array_unique($current_users);
				
			yaexam_exam_set_settings($this->id, 'assigned_users', $users_save);
		}
		
		
		return $users_response;
	}
	
	public function remove_assigned_users( $ids ){
		
		if(!$ids) return false;
		
		if(!is_array($ids)){
			$ids	=	array($ids);
		}
				
		$ids_remove	=	array();
		
		foreach($ids as $id){
			
			$id	=	absInt($id);
			
			if($id && $this->is_assigned_user($id)){
				
				array_push($ids_remove, $id);
			}
			
		}
		
		if($ids_remove){
			
			$settings		=	$this->get_settings();
			
			$ids_save		=	array();
			
			if(isset($settings['assigned_users']) && is_array($settings['assigned_users']))
			{
				$ids_save	=	array_diff($settings['assigned_users'], $ids_remove);
				
				foreach($ids_save as $key => $userid ){

					if(is_array($userid)){
						unset($ids_save[$key]);
					}
				}
			}
			
			yaexam_exam_set_settings($this->id, 'assigned_users', $ids_save);
			
			
			foreach($ids_remove as $id){
				
				$user_exams	=	yaexam_get_user_meta( $id, 'exams' );
									
				if( $user_exams && is_array( $user_exams ) ){
					
					$user_exams	=	array_unique( array_diff( $user_exams, array( $this->id ) ) );
					
				}else{
					
					$user_exams	=	array( $this->id );
				}
				
				yaexam_update_user_meta( $id, array( 'exams' => $user_exams ) );
				
			}

			return true;
		}

		return false;
	}
	
	public function is_assigned_user( $user_id ){
		
		$settings	=	$this->get_settings();
		
		if(isset($settings['assigned_users']) && is_array($settings['assigned_users']))
		{
			return in_array($user_id, $settings['assigned_users']);
		}
		
		return false;
	}
	
	public function update_played() {
				
		$played	=	$this->played;
		
		$played	=	is_numeric( $played ) ? $played : 0;
		
		if( $played )
		{
			
			$played++;
			
		}else{
			
			$played = 1;
		}
		
		update_post_meta( $this->id, '_played', $played);
	}
	
	public function reset_played() {
		
		update_post_meta( $this->id, '_played', 0);
	}
	
	public function remove_membership( $membership_id ) {
		
		$settings	=	$this->get_settings();
		
		$memberships	=	$settings['memberships'];
		
		if(in_array($membership_id, $memberships)){
			foreach($memberships as $index => $m){
				if($m == $membership_id){
					unset($memberships[$index]);
				}
			}
			
			$this->set_settings('memberships', array_values($memberships));
		}
		
	}

	public function export_csv() {

		
	}
}