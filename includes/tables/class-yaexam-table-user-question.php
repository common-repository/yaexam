<?php

namespace YaExam\Tables;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class YAEXAM_Table_User_Question extends YAEXAM_Table
{
	protected	$_table	=	'user_questions';
	protected	$_key	=	'id';
	
	protected $fields	=	array(
        'id'			    =>	'd',
        'user_id'           =>  'd',
        'question_id'       =>  'd',
		'question_order'    =>  'd',
		'session_id'        =>  'd',
		'category_id'	    =>	'd',
		'title'			    =>	's',
		'slug'			    =>	's',
		'content'		    =>	's',
		'score'			    =>	'd',
		'answer_type'	    =>	's',
		'order_type'	    =>	'd',
		'explanation'	    =>	's',
		'timeout'		    =>	'd',
		'answers'		    =>	's',
		'params'		    =>	's',
	);
	
}