<?php

namespace YaExam\Tables;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class YAEXAM_Table_Exam_Question extends YAEXAM_Table
{
	protected	$_table	=	'exam_questions';
	protected	$_key	=	'id';
	
	protected $fields	=	array(
		'id'				=>	'd',
		'exam_id'			=>	'd',
		'question_type'		=>	's',
		'question_id'		=>	'd',
		'question_params' 	=> 	's',
		'question_order'	=> 	'd',
		'title'				=>	's',
        'state'				=>	's',
	);
	
}