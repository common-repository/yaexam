<?php

namespace YaExam\Tables;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class YAEXAM_Table_Exam_Result extends YAEXAM_Table
{
	protected	$_table	=	'exam_results';
	protected	$_key	=	'id';
	
	protected $fields	=	array(
		'id'				=>	'd',
		'exam_id'			=>	'd',
        'user_id'           =>  'd',
        'user_attempts'     =>  'd',
	);
	
}