<?php

namespace YaExam\Tables;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class YAEXAM_Table_Question_Category extends YAEXAM_Table
{
	protected	$_table	=	'question_categories';
	protected	$_key	=	'id';
	
	protected $fields	=	array(
		'id'			=>	'd',
		'name'			=>	's',
		'content'		=>	's',
		'total'			=>	'd',
	);
	
	public static function increase_total( $id ) {

		global $wpdb;

		if( !$id ) { return; }

		$table = $wpdb->prefix . 'question_categories';

		$total = $wpdb->get_var( $wpdb->prepare('SELECT total FROM ' . $table . ' WHERE id = %d', $id) );

		$wpdb->update( $table, ['total' => ($total + 1)], ['%d'], ['%d'] );
	}

	public static function decrease_total( $id ) {

		global $wpdb;

		if( !$id ) { return; }

		$table = $wpdb->prefix . 'question_categories';

		$total = $wpdb->get_var( $wpdb->prepare('SELECT total FROM ' . $table . ' WHERE id = %d', $id) );

		if( $total > 0 ) {
			$wpdb->update( $table, ['total' => ($total - 1)], ['%d'], ['%d'] );
		}
	}
}