<?php

namespace YaExam\Admin;

use YaExam\YAEXAM_Exam;

defined( 'ABSPATH' ) || exit;
class YAEXAM_Admin_Results {

	public static function output() {

		if( isset($_GET['id']) && isset($_GET['user_id']) ) {
					
			self::_result();
			
		}else{
			
			self::_results();
		}
	}

	public static function _results() {

		$exams = yaexam_get_exams(['meta_query' => []]);
		
		$exam = isset($_GET['exam']) ? yaexam_clean_int($_GET['exam']): 0;
		$exam = !isset($_GET['exam']) && $exams ? yaexam_clean_int($exams[0]->ID) : $exam;

		if( isset($_GET['action']) && $_GET['action'] == 'filter' && isset($_GET['exam']) ) {

			$exam = isset($_GET['exam']) ? yaexam_clean_int($_GET['exam']) : 0;
		}
		
		$page = isset($_GET['p']) ? yaexam_clean_int($_GET['p']) : 1;
		
		$results = [];
		
		$paginated = yaexam_get_paginated_results(['page' => $page, 'exam' => $exam]);
		
		$results = $paginated['data'];

		include 'views/html-admin-results.php';
	}
	
	public static function _result() {

		$exam_id = yaexam_clean_int($_GET['id']);
		$user_id = yaexam_clean_int($_GET['user_id']);

		if( !$exam_id || !$user_id ) return;
		
		if( isset($_GET['action']) ) {

			switch( $_GET['action'] ){

				case 'remove':

					$ids = isset($_GET['yaexam_checkall']) && yaexam_clean_int($_GET['yaexam_checkall']) ? $_GET['yaexam_checkall'] : [];
			
					if( $ids ) {
						
						yaexam_remove_results( $ids );

						yaexam_exam_remove_user_attempt( $exam_id, $user_id );
					}

				break;

				case 'update_attempt':

					$attempt = isset($_GET['attempt']) ? yaexam_clean_int($_GET['attempt']) : 0;

					yaexam_exam_get_user_attempt_by_id( $exam_id, $user_id, $attempt );

					$is_updated_attempt = true;

				break;
			}
			
			
		}

		$exam = new YAEXAM_Exam($exam_id);
		$user = get_user_by('id', $user_id);

		$exam_link = get_permalink( $exam_id );

		$results = yaexam_get_results(['exam_id' => $exam_id, 'user_id' => $user_id]);
		$attempt = yaexam_exam_get_user_attempt( $exam_id, $user_id );

		$user_attempts = $attempt ? $attempt['user_attempts'] : 0;

		include 'views/html-admin-result.php';
	}
}