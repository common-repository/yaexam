<?php

namespace YaExam\Tables;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class YAEXAM_Table_User_Session extends YAEXAM_Table
{
	protected	$_table	=	'user_sessions';
	protected	$_key	=	'id';
	
	protected $fields	=	array(
        'id'			    =>	'd',
        'user_id'           =>  'd',
        'exam_id'           =>  'd',
        'state'             =>  's',
        'params'            =>  's'
	);
	
}