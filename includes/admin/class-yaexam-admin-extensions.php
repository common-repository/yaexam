<?php

namespace YaExam\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class YAEXAM_Admin_Extensions {
	
	private static $errors   = array();

	private static $messages = array();
	
	public static function output() {

		$extensions 	= self::get_extensions();

		if(isset($_GET['active'])) {

			$addon = yaexam_clean(wp_unslash($_GET['active']));			

			if( file_exists( WP_PLUGIN_DIR . $addon ) ) {

				activate_plugin( $addon . '/'. $addon .'.php' );
				
				wp_redirect(admin_url('admin.php?page=yaexam-extensions'));
			}
		}

		if(isset($_GET['deactive'])) {

			$addon = yaexam_clean(wp_unslash($_GET['deactive']));

			if( file_exists( WP_PLUGIN_DIR . $addon ) ) {

				deactivate_plugins( [$addon . '/' . $addon . '.php'] );
				
				wp_redirect(admin_url('admin.php?page=yaexam-extensions'));
			}
		}

		if( $extensions ) {
			
			foreach( $extensions as &$ex ) {

				$ex['status'] = 0;

				if( file_exists( WP_PLUGIN_DIR . $ex['slug'] ) ) {

					$ex['status'] = 1;
					$ex['active'] = admin_url('admin.php?page=yaexam-extensions&active=' . $ex['slug']);
					$ex['deactive'] = admin_url('admin.php?page=yaexam-extensions&deactive=' . $ex['slug']);

					if(is_plugin_active($ex['slug'] . '/' . $ex['slug'] . '.php')){

						$ex['status'] = 2;
					}
				}
			}
		}

		
		
		include 'views/html-admin-extensions.php';
	}


	public static function get_extensions() {

		$thumbnail_uri = YAEXAM_URI . 'assets/images/';

		$extensions = [
			['name' => 'YaExam Certificate', 'slug' => 'yaexam-certificate', 'thumbnail' => $thumbnail_uri . 'certificate.png', 'detail' => 'https://yaexam.com/addons/yaexam-certificate/', 'price' => 35, 'demo' => 'http://certificate.yaexam.com/', 'description' => 'Adding the certificate for exams when users finished'],
			['name' => 'YaExam PDF', 'slug' => 'yaexam-pdf', 'thumbnail' => $thumbnail_uri . 'pdf.png', 'demo' => 'http://pdf.yaexam.com/', 'detail' => 'https://yaexam.com/addons/yaexam-pdf/', 'price' => 35, 'description' => 'Export your exam to pdf'],
			['name' => 'YaExam Woocommerce', 'slug' => 'yaexam-woocommerce', 'thumbnail' => $thumbnail_uri . 'yaexam_woocommerce.png', 'detail' => 'https://yaexam.com/addons/yaexam-woocommerce/', 'price' => 35, 'demo' => 'http://woocommerce.yaexam.com/', 'description' => 'Sale your exam by using Woocommerce'],

		];

		if( $extensions ) {

			return $extensions;
			
		}else {

			return false;
		}
	}
}
