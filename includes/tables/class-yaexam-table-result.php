<?php

namespace YaExam\Tables;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class YAEXAM_Table_Result extends YAEXAM_Table
{
	protected	$_table	=	'results';
	protected	$_key	=	'id';
	
	protected $fields	=	array(
		'id'			=>	'd',
		'exam_id'		=>	'd',
		'ranking_id'	=>	'd',
		'user_id'		=>	'd',
		'user_name'		=>	's',
		'user_email'	=>	's',
		'user_ip'		=>	's',
		'user_meta'		=>	's',
		'score'			=>	'd',
		'percent'		=>	'd',
		'total_score'	=>	'd',
		'duration'		=>	's',
		'total_corrects'		=>	'd',
		'total_wrongs'			=>	'd',
		'total_notanswereds'	=>	'd',
		'exam_duration'			=>	'd',
		'date_start'			=>	's',
		'date_end'				=>	's',
		'others'				=>	's'
	);

}