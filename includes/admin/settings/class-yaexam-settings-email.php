<?php

namespace YaExam\Admin\Settings;

use YaExam\Admin\Settings\YAEXAM_Settings_Page;
use YaExam\Admin\YAEXAM_Admin_Settings;

defined( 'ABSPATH' ) || exit;
class YAEXAM_Settings_Email extends YAEXAM_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'email';
		$this->label = __( 'Email', 'yaexam' );

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
        
        $siteInfo = get_bloginfo();

		$settings = apply_filters( 'yaexam_email_settings', array(

            array( 'title' => __( 'New Result', 'yaexam' ), 'type' => 'title', 'desc' => '', 'id' => 'email_new_result' ),

                array(
                    'title'    => __( 'Subject', 'yaexam' ),
                    'id'       => 'yaexam_email_setting_new_result_subject',
                    'type'     => 'text',
                    'default'  => 'Result: [{exam_title}]',
                    'class'    => 'em-full-width',
                    'desc_tip' => true,
                ),
                
                array(
                    'title'    => __( 'Header', 'yaexam' ),
                    'id'       => 'yaexam_email_setting_new_result_header',
                    'type'     => 'textarea',
                    'default'  => '[{exam_title}]',
                    'class'    => 'em-full-width',
                    'rows'      => 5,
                    'desc_tip' => true,
                ),

                array(
                    'title'    => __( 'Content', 'yaexam' ),
                    'id'       => 'yaexam_email_setting_new_result_content',
                    'type'     => 'textarea',
                    'default'  => '<p>Hi, [{user_nicename}]!</p><p><a href="[{examl_link}]">[{exam_title}]</a></p><p>
                        <ul>
                            <li><strong>Score</strong> : [{score}] / [{total_score}]</li>
                            <li><strong>Duration</strong> : [{duration}]</li>
                            <li><strong>Date</strong> : [{date}]</li>
                        </ul></p>',
                    'class'    => 'em-full-width',
                    'rows'      => 10,
                    'desc_tip' => true,
                ),

                array(
                    'title'    => __( 'CC Admin', 'yaexam' ),
                    'desc'     => __( 'admin_1@abc.com,admin_2@abc.com', 'yaexam' ),
                    'id'       => 'yaexam_email_notification_new_result_recipient',
                    'type'     => 'text',
                    'default'  => '',
                    'class'    => 'em-full-width',
                    'desc_tip' => true,
                ),

            array( 'type' => 'sectionend', 'id' => 'email_new_result' ),

            array( 'title' => __( 'General', 'yaexam' ), 'type' => 'title', 'desc' => '', 'id' => 'email_general' ),

            array(
				'title'    => __( 'Footer', 'yaexam' ),
				'id'       => 'yaexam_email_setting_footer',
				'type'     => 'textarea',
				'default'  => $siteInfo,
                'class'    => 'em-full-width',
                'rows'      => 3,
				'desc_tip' => true,
            ),

            array( 'type' => 'sectionend', 'id' => 'email_general' ),

			array( 'title' => __( 'User Registered', 'yaexam' ), 'type' => 'title', 'desc' => '', 'id' => 'email_user_registered' ),

                array(
                    'title'    => __( 'Subject', 'yaexam' ),
                    'id'       => 'yaexam_email_setting_user_registered_subject',
                    'type'     => 'text',
                    'default'  => 'Welcome to [{site_name}]',
                    'class'    => 'em-full-width',
                    'desc_tip' => true,
                ),
                
                array(
                    'title'    => __( 'Header', 'yaexam' ),
                    'id'       => 'yaexam_email_setting_user_registered_header',
                    'type'     => 'textarea',
                    'default'  => 'Welcome to [{site_name}]',
                    'class'    => 'em-full-width',
                    'rows'      => 5,
                    'desc_tip' => true,
                ),

                array(
                    'title'    => __( 'Content', 'yaexam' ),
                    'id'       => 'yaexam_email_setting_user_registered_content',
                    'type'     => 'textarea',
                    'default'  => '<p>Your username is <strong>[{user_name}]</strong></p>
                    <p>We hope that you enjoy your stay with us! :-) </p>
                    <p>We happy to support you</p>',
                    'class'    => 'em-full-width',
                    'rows'      => 10,
                    'desc_tip' => true,
                ),
			
            array( 'type' => 'sectionend', 'id' => 'email_user_registered' ),
            
            array( 'title' => __( 'Reset Password', 'yaexam' ), 'type' => 'title', 'desc' => '', 'id' => 'email_reset_password' ),

                array(
                    'title'    => __( 'Subject', 'yaexam' ),
                    'id'       => 'yaexam_email_setting_reset_password_subject',
                    'type'     => 'text',
                    'default'  => 'RESET PASSWORD',
                    'class'    => 'em-full-width',
                    'desc_tip' => true,
                ),
                
                array(
                    'title'    => __( 'Header', 'yaexam' ),
                    'id'       => 'yaexam_email_setting_reset_password_header',
                    'type'     => 'textarea',
                    'default'  => 'RESET PASSWORD',
                    'class'    => 'em-full-width',
                    'rows'      => 5,
                    'desc_tip' => true,
                ),

                array(
                    'title'    => __( 'Content', 'yaexam' ),
                    'id'       => 'yaexam_email_setting_reset_password_content',
                    'type'     => 'textarea',
                    'default'  => '<p>Hello,</p><p>Someone requested that the password be reset for the following account:</p>
                    <p>Username: [{user_login}]</p>
                    <p>If this was a mistake, just ignore this email and nothing will happen.</p>
                    <p>To reset your password, visit the following address:</p>
                    <p><a href="[{link_reset}]">Click here to reset your password</a></p>',
                    'class'    => 'em-full-width',
                    'rows'      => 10,
                    'desc_tip' => true,
                ),

            array( 'type' => 'sectionend', 'id' => 'email_reset_password' ),

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
		echo '<div class="color_box">' . wc_help_tip( $desc ) . '
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

return new YAEXAM_Settings_Email();
