<?php

namespace YaExam;

defined( 'ABSPATH' ) || exit;
class YAEXAM_Template_Loader {
	public static function init() {
		add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );
	}
	
	public static function template_loader( $template ) {
		$find = array( 'yaexam.php' );
		$file = '';
		
		if ( is_single() && get_post_type() == 'exam' ) {

			$file 	= 'single-exam.php';
			$find[] = $file;
			$find[] = YAEXAM()->template_path() . $file;
			
		} elseif ( is_exam_taxonomy() ) {
			
			$term   = get_queried_object();

			if ( is_tax( 'exam_cat' ) || is_tax( 'exam_tag' ) ) {
				$file = 'taxonomy-' . $term->taxonomy . '.php';
			} else {
				$file = 'archive-exam.php';
			}

			$find[] = 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
			$find[] = YAEXAM()->template_path() . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
			$find[] = 'taxonomy-' . $term->taxonomy . '.php';
			$find[] = YAEXAM()->template_path() . 'taxonomy-' . $term->taxonomy . '.php';
			$find[] = $file;
			$find[] = YAEXAM()->template_path() . $file;
			
		} elseif ( is_post_type_archive( 'exam' ) ) {

			$file 	= 'archive-exam.php';
			$find[] = $file;
			$find[] = YAEXAM()->template_path() . $file;

		} elseif ( is_single() && get_post_type() == 'emquestion' ) {

			$file 	= 'single-question.php';
			$find[] = $file;
			$find[] = YAEXAM()->template_path() . $file;
		}
		
		if ( $file ) {
			$template       = locate_template( array_unique( $find ) );
			if ( ! $template ) {
				$template = YAEXAM()->plugin_path() . '/templates/' . $file;
			}
		}
		
		return $template;
	}
}

YAEXAM_Template_Loader::init();