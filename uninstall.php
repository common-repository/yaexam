<?php

use YaExam\YAEXAM_Install;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$is_remove_data = get_option( 'yaexam_is_uninstall_remove_data', 'no' );

if ( $is_remove_data == 'yes' ) {
	
	include_once( 'includes/class-yaexam-install.php' );
	YAEXAM_Install::remove_roles();
	
	wp_trash_post( get_option( 'yaexam_archive_exam_page_id' ) );
	wp_trash_post( get_option( 'yaexam_myaccount_page_id' ) );
	wp_trash_post( get_option( 'yaexam_register_page_id' ) );
	wp_trash_post( get_option( 'yaexam_login_page_id' ) );
	wp_trash_post( get_option( 'yaexam_memberships_page_id' ) );
	
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaexam_questions" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaexam_question_categories" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaexam_exam_sessions" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaexam_results" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaexam_sessions" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaexam_exam_questions" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaexam_exam_results" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaexam_save_later" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaexam_user_questions" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}yaexam_user_sessions" );
	
	$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'yaexam\_%';");
	
	// Delete posts + data.
	$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ('exam');" );
	$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );
	
	foreach ( array( 'exam_cat' ) as $taxonomy ) {
		$wpdb->delete(
			$wpdb->term_taxonomy,
			array(
				'taxonomy' => $taxonomy,
			)
		);
	}
	
	wp_cache_flush();
}
