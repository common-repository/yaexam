<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function yaexam_message_count( $message_type = '' ) {
	if ( ! did_action( 'yaexam_init' ) ) {
		return;
	}

	$message_count = 0;
	$all_messages  = YAEXAM()->session->get( 'yaexam_messages', array() );
	
	if ( isset( $all_messages[$message_type] ) ) {

		$message_count = absint( sizeof( $all_messages[$message_type] ) );

	} elseif ( empty( $message_type ) ) {

		foreach ( $all_messages as $messages ) {
			$message_count += absint( sizeof( $all_messages ) );
		}

	}

	return $message_count;
}

function yaexam_has_message( $message, $message_type = 'success' ) {
	if ( ! did_action( 'yaexam_init' ) ) {
		return false;
	}

	$messages = YAEXAM()->session->get( 'yaexam_messages', array() );
	$messages = isset( $messages[ $message_type ] ) ? $messages[ $message_type ] : array();
	return array_search( $message, $messages ) !== false;
}

function yaexam_add_message( $message, $message_type = 'success' ) {
	if ( ! did_action( 'yaexam_init' ) ) {
		return;
	}

	$messages = YAEXAM()->session->get( 'yaexam_messages', array() );

	$messages[$message_type][] = apply_filters( 'yaexam_add_' . $message_type, $message );
	
	YAEXAM()->session->set( 'yaexam_messages', $messages );
}

function yaexam_clear_messages() {
	if ( ! did_action( 'yaexam_init' ) ) {
		return;
	}
	
	YAEXAM()->session->set( 'yaexam_messages', null );
}

function yaexam_print_messages() {
	if ( ! did_action( 'yaexam_init' ) ) {
		return;
	}

	$all_messages  = YAEXAM()->session->get( 'yaexam_messages', array() );
	$message_types = apply_filters( 'yaexam_message_types', array( 'error', 'success', 'notice' ) );
	
	foreach ( $message_types as $message_type ) {
		if ( yaexam_message_count( $message_type ) > 0 ) {
			yaexam_get_template( "messages/{$message_type}.php", array(
				'messages' => $all_messages[$message_type]
			) );
		}
	}

	yaexam_clear_messages();
}
