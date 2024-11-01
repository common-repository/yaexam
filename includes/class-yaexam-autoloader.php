<?php

namespace YaExam;

defined( 'ABSPATH' ) || exit;
class YAEXAM_Autoloader {

	private $include_path = '';
	
	public function __construct() {

		$this->include_path = untrailingslashit( YAEXAM_PLUGIN_DIR ) . '/includes/';
		
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		
	}

	private function get_file_name_from_class( $class ) {

		return 'class-' . str_replace( '_', '-', $class ) . '.php';
	}

	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once( $path );
			return true;
		}
		return false;
	}

	public function autoload( $class ) {

		$classes = explode('\\', strtolower( $class ));

		$class = array_pop($classes);
		
		$file  = $this->get_file_name_from_class( $class );
		$path  = '';
		
		if ( strpos( $class, 'yaexam_shortcode_' ) === 0 ) {
			$path = $this->include_path . 'shortcodes/';
		} elseif ( strpos( $class, 'yaexam_meta_box' ) === 0 ) {
			$path = $this->include_path . 'admin/meta-boxes/';
		} elseif ( strpos( $class, 'yaexam_admin' ) === 0 ) {
			$path = $this->include_path . 'admin/';
		} elseif ( strpos( $class, 'yaexam_table' ) === 0 ) {
			$path = $this->include_path . 'tables/';
		} elseif ( strpos( $class, 'yaexam_addon_' ) === 0 ) {
			$path = $this->include_path . 'addons/';
		}else{

			$path = $this->include_path . $file;
		}

		

		if ( empty( $path ) || ( ! $this->load_file( $path . $file ) && strpos( $class, 'yaexam_' ) === 0 ) ) {
			
			$this->load_file( $this->include_path . $file );
		}
	}
}