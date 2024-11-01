<?php

namespace YaExam\Admin\Settings;

use YaExam\Admin\Settings\YAEXAM_Settings_Page;
use YaExam\Admin\YAEXAM_Admin_Settings;

defined( 'ABSPATH' ) || exit;
class YAEXAM_Settings_General extends YAEXAM_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'general';
		$this->label = __( 'General', 'yaexam' );

		add_filter( 'yaexam_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'yaexam_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'yaexam_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		$GLOBALS['hide_save_button'] = false;

		$settings = apply_filters( 'yaexam_general_settings', array(

			array( 'title' => __( 'General Options', 'yaexam' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),
			
			array(
				'title'    => __( 'Archive Page', 'yaexam' ),
				'desc'     => __( 'Page contents:', 'yaexam' ),
				'id'       => 'yaexam_archive_exam_page_id',
				'type'     => 'single_select_page',
				'default'  => '',
				'class'    => 'em-enhanced-select',
				'css'      => 'min-width:300px;',
				'desc_tip' => true,
			),
			
			array(
				'title'    => __( 'My Account Page', 'yaexam' ),
				'desc'     => __( 'Page contents:', 'yaexam' ) . ' [' . apply_filters( 'yaexam_my_account_shortcode_tag', 'yaexam_my_account' ) . ']',
				'id'       => 'yaexam_myaccount_page_id',
				'type'     => 'single_select_page',
				'default'  => '',
				'class'    => 'em-enhanced-select',
				'css'      => 'min-width:300px;',
				'desc_tip' => true,
			),
			
			array(
				'title'    => __( 'Register Page', 'yaexam' ),
				'desc'     => __( 'Page contents:', 'yaexam' ) . ' [' . apply_filters( 'yaexam_register_shortcode_tag', 'yaexam_register' ) . ']',
				'id'       => 'yaexam_register_page_id',
				'type'     => 'single_select_page',
				'default'  => '',
				'class'    => 'em-enhanced-select',
				'css'      => 'min-width:300px;',
				'desc_tip' => true,
			),
			
			array(
				'title'    => __( 'Login Page', 'yaexam' ),
				'desc'     => __( 'Page contents:', 'yaexam' ) . ' [' . apply_filters( 'yaexam_login_shortcode_tag', 'yaexam_login' ) . ']',
				'id'       => 'yaexam_login_page_id',
				'type'     => 'single_select_page',
				'default'  => '',
				'class'    => 'em-enhanced-select',
				'css'      => 'min-width:300px;',
				'desc_tip' => true,
			),

			array(
				'desc'     => __( 'Remove data when uninstall', 'yaexam' ),
				'id'       => 'yaexam_is_uninstall_remove_data',
				'title'    => __( 'Remove Data', 'yaexam' ),
				'type'     => 'checkbox',
				'default'  => 'no'
			),

			array( 'type' => 'sectionend', 'id' => 'general_options' ),
			
			array( 'title' => __( 'Display Options', 'yaexam' ), 'type' => 'title', 'desc' => '', 'id' => 'display_options' ),
	
			array(
				'title'       => __( 'Exams per Page', 'yaexam' ),
				'id'          => 'yaexam_posts_per_page',
				'type'        => 'text',
				'css'         => 'min-width:300px;',
				'default'     => 10,
				'autoload'    => false,
				'desc_tip'    => true
			),
			
			array(
				'title'       => __( 'Default Order', 'yaexam' ),
				'id'          => 'yaexam_default_exam_orderby',
				'type'        => 'select',
				'default'     => 'date',
				'options'	  => apply_filters( 'yaexam_catalog_orderby', array(
												'menu_order' 	=> __( 'Default sorting', 'yaexam' ),
												'date'			=> __( 'Sort by newness', 'yaexam' ),
												'duration-asc'  => __( 'Sort by duration: low to high', 'yaexam' ),
												'duration-desc' => __( 'Sort by duration: high to low', 'yaexam' ),
											) )
			),

			array( 'type' => 'sectionend', 'id' => 'display_options' ),

		) );

		return apply_filters( 'yaexam_get_settings_' . $this->id, $settings );
	}

	/**
	 * Output a colour picker input box.
	 *
	 * @param mixed $name
	 * @param string $id
	 * @param mixed $value
	 * @param string $desc (default: '')
	 */
	public function color_picker( $name, $id, $value, $desc = '' ) {
		echo '<div class="color_box">
			<input name="' . esc_attr( $id ). '" id="' . esc_attr( $id ) . '" type="text" value="' . esc_attr( $value ) . '" class="colorpick" /> <div id="colorPickerDiv_' . esc_attr( $id ) . '" class="colorpickdiv"></div>
		</div>';
	}

	/**
	 * Save settings.
	 */
	public function save() {
		$settings = $this->get_settings();

		YAEXAM_Admin_Settings::save_fields( $settings );
	}

}

return new YAEXAM_Settings_General();
