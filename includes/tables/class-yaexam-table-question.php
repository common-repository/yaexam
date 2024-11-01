<?php

namespace YaExam\Tables;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class YAEXAM_Table_Question extends YAEXAM_Table
{
	protected	$_table	=	'questions';
	protected	$_key	=	'id';
	
	protected $fields	=	array(
		'id'			=>	'd',
		'category_id'	=>	'd',
		'title'			=>	's',
		'slug'			=>	's',
		'content'		=>	's',
		'score'			=>	'd',
		'video'			=>	's',
		'answer_type'	=>	's',
		'order_type'	=>	'd',
		'explanation'	=>	's',
		'timeout'		=>	'd',
		'answers'		=>	's',
		'params'		=>	's',
	);
	
}