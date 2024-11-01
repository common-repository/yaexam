<?php

namespace YaExam\Tables;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class YAEXAM_Table_Certificate extends YAEXAM_Table
{
	protected	$_table	=	'certificates';
	protected	$_key	=	'id';
	
	protected $fields	=	array(
		'id'			=>	'd',
		'user_id'		=>	'd',
		'cert_id'		=>	'd',
		'tests'			=>	's',
		'is_passed'		=>	'd'
	);

}