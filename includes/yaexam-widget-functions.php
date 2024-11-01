<?php
/**
 * YAExam Widget Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include widget classes.
include_once( 'abstracts/abstract-yaexam-widget.php' );
include_once( 'widgets/class-yaexam-widget-exams.php' );
include_once( 'widgets/class-yaexam-widget-exam-categories.php' );

function yaexam_register_widgets() {
	
	register_widget( 'yaexam_Widget_Exam_Categories' );
	register_widget( 'yaexam_Widget_Exams' );

	register_sidebar( array(
			'id'	=>	__(	'yaexam-left-sidebar', 'yaexam' ),
			'name'	=>	__( 'YaExam Left Sidebar', 'yaexam'),
			'before_widget' => '<div id="%1$s" class="mb-3 box-sidebar yaexam-sidebar-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h5>',
			'after_title'  => '</h5>',
		) );
			
	register_sidebar( array(
		'id'	=>	__(	'yaexam-right-sidebar', 'yaexam' ),
		'name'	=>	__( 'YaExam Right Sidebar', 'yaexam'),
		'before_widget' => '<div id="%1$s" class="box-sidebar yaexam-sidebar-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h5>',
		'after_title'  => '</h5>',
	) );
}
add_action( 'widgets_init', 'yaexam_register_widgets' );

