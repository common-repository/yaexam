<?php

namespace YaExam\Admin;

use YaExam\Admin\YAEXAM_Admin_Settings;
// use YaExam\Admin\YAEXAM_Admin_Questions;
// use YaExam\Admin\YAEXAM_Admin_Dashboard;
// use YaExam\Admin\YAEXAM_Admin_Results;

defined( 'ABSPATH' ) || exit;

/**
 * YAEXAM_Admin_Menus Class.
 */
class YAEXAM_Admin_Menus {
	
	public function __construct() {
		
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 10 );
		
		add_filter( 'menu_order', array( $this, 'menu_order' ) );
		add_filter( 'custom_menu_order', array( $this, 'custom_menu_order' ) );
		
	}
	
	public function admin_menu() {
		global $menu;

		if ( current_user_can( 'manage_yaexam' ) ) {
			$menu[] = array( '', 'read', 'separator-yaexam', '', 'wp-menu-separator yaexam' );
		}

		add_menu_page(	__( 'YaExam', 'yaexam' ), 
						__( 'YaExam', 'yaexam' ), 
						'manage_yaexam', 'yaexam', 
						array( $this, 'dashboard_page' ), 'dashicons-admin-post', 56
					);

		add_submenu_page(	'yaexam', 
							__( 'Dashboard', 'yaexam' ),
							__( 'Dashboard', 'yaexam' ) , 
							'manage_yaexam', 
							'yaexam' );

		add_submenu_page(	'yaexam', 
							__( 'Questions', 'yaexam' ),
							__( 'Questions', 'yaexam' ) , 
							'manage_yaexam', 
							'em-questions', array( $this, 'questions_page' ) );

		add_submenu_page(	'yaexam', 
							__( 'Settings', 'yaexam' ),
							__( 'Settings', 'yaexam' ) , 
							'manage_yaexam', 
							'em-settings', array( $this, 'settings_page' ) );

		add_submenu_page(	'yaexam', 
							__( 'Extensions', 'yaexam' ),
							__( 'Extensions', 'yaexam' ) , 
							'manage_yaexam', 
							'em-extensions', array( $this, 'extensions_page' ) );

		add_submenu_page(	'edit.php?post_type=exam', 
							__( 'Results', 'yaexam' ),
							__( 'Results', 'yaexam' ) , 
							'manage_yaexam', 
							'em-results', array( $this, 'results_page' ) );
	}

	public function questions_page() {

		
		YAEXAM_Admin_Questions::output();
		
	}

	public function results_page() {

		YAEXAM_Admin_Results::output();
	}
	
	public function dashboard_page() {
		
		YAEXAM_Admin_Dashboard::output();
	}
	
	public function settings_page() {
		
		YAEXAM_Admin_Settings::output();
		
	}

	public function extensions_page() {
		
		YAEXAM_Admin_Extensions::output();
		
	}

	public function importing_questions_page() {

		YAEXAM_Admin_Importing_Questions::output();
	}
	
	public function menu_order( $menu_order ) {
		// Initialize our custom order array
		$yaexam_menu_order = array();

		// Get the index of our custom separator
		$yaexam_separator = array_search( 'separator-yaexam', $menu_order );

		$yaexam_test 	= array_search( 'edit.php?post_type=exam', $menu_order );
		$yaexam_question = array_search( 'edit.php?post_type=emquestion', $menu_order );

		// Loop through menu order and do some rearranging
		foreach ( $menu_order as $index => $item ) {

			if ( ( ( 'yaexam' ) == $item ) ) {
				$yaexam_menu_order[] = 'separator-yaexam';
				$yaexam_menu_order[] = $item;
				$yaexam_menu_order[] = 'edit.php?post_type=exam';
				$yaexam_menu_order[] = 'edit.php?post_type=examlevel';
				$yaexam_menu_order[] = 'admin.php?page=yaexam-woo';
				$yaexam_menu_order[] = 'edit.php?post_type=emquestion';
				unset( $menu_order[ $yaexam_separator ] );
				unset( $menu_order[ $yaexam_test ] );
				unset( $menu_order[ $yaexam_question ] );
			} elseif ( !in_array( $item, array( 'separator-yaexam' ) ) ) {
				$yaexam_menu_order[] = $item;
			}

		}

		// Return order
		return $yaexam_menu_order;
	}
	
	/**
	 * Custom menu order.
	 *
	 * @return bool
	 */
	public function custom_menu_order() {
		return current_user_can( 'manage_yaexam' );
	}
	
	
}

new YAEXAM_Admin_Menus();