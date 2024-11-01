<?php

namespace YaExam;

defined( 'ABSPATH' ) || exit;

use YaExam\YAEXAM_Exam;

class YAEXAM_Exam_Factory {
	
	public function get_exam( $the_exam = false, $args = array() ) {
		
		if ( false === $the_exam ) {
			$the_exam = $GLOBALS['post'];
		} elseif ( is_numeric( $the_exam ) ) {
			$the_exam = get_post( $the_exam );
		} elseif ( $the_exam instanceof YAEXAM_Exam ) {
			$the_exam = get_post( $the_exam->id );
		}
		
		if ( ! $the_exam ) {
			return false;
		}
		
		return new YAEXAM_Exam( $the_exam, $args );
	}
}

