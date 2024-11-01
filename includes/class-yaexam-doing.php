<?php

namespace YaExam;

defined( 'ABSPATH' ) || exit;

class YAEXAM_Doing {
				
	public static function instance() {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	}
	
	public function start_exam() 
	{	
		global $wpdb, $exam;
		
		$exam_id	=	absInt($_POST['id']);
		$user_id	=	get_current_user_id();
		
		do_action( 'yaexam_before_start_exam', $exam_id );
		
		if( !yaexam_can_do_exam($exam_id, $user_id) ) {
			
			yaexam_add_message( __( 'Sorry! You have not permession do this exam', 'yaexam' ), 'error' );
			
			wp_safe_redirect( get_permalink($exam_id) );
			exit;
			
		}
				
		$exam	=	YAEXAM()->exam_factory->get_exam($exam_id);

		$doing_data	=	$exam->get_doing_data();
		
		if( !$doing_data ) {
			
			yaexam_add_message( __( 'Sorry! You can not do this exam', 'yaexam' ), 'error' );
			
			wp_safe_redirect( get_permalink($exam_id) );
			exit;
		}
		
		if( $doing_data ){

			$time_started 	= 	yaexam_get_date('now')->format('U');
			$duration		=	yaexam_get_post_meta($exam_id, 'duration');
			$save_later		=	yaexam_get_post_meta($exam_id, 'save_later');
			
			$session_id = yaexam_init_user_session([
					'user_id' 		=>  $user_id,
					'exam_id'		=>	$exam_id,
					'state'			=>	'doing',
					'time_started' 	=>  $time_started,
					'time_passed'  	=>  0,
					'duration'		=>	$duration,
					'save_later'	=>	$save_later,
					'params'		=>	[],
					'questions'		=>	$doing_data['questions']
				]);
				
			$redirect_url = add_query_arg( 'em-doing', $session_id, $exam->get_permalink() );
			
			wp_redirect(apply_filters( 'yaexam_start_doing_redirect', $redirect_url ));
			exit;
		}
		
	}

	public function doing_question( $sid, $page )
	{				
		$user_question = yaexam_get_doing_user_question( $sid, $page );

		$question = $user_question['question_data'];
		
		ob_start();

		yaexam_get_template('questions/' . $question['answer_type'] . '.php', $question);
		
		return ob_get_clean();
	}

	public function update_question( $sid, $page, $answered ) {

		$sid = yaexam_clean(wp_unslash($sid));
		$page = yeaxam_clean_int($page);

		$question = yaexam_get_doing_user_question( $sid, $page );

		$question_result = yaexam_question_is_correct( $question['question_data'], $answered );

		yaexam_update_doing_question( $sid, $page, [
			'question_answered' =>	maybe_serialize($answered),
			'question_result'	=>	($question_result ? 'right' : 'wrong' )
		], ['%s', '%s']);
	}
	
}