<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'YAEXAM_Admin_Post_Types' ) ) :

class YAEXAM_Admin_Post_Types {
	public function __construct() {
		
		add_filter( 'manage_exam_posts_columns', array( $this, 'exam_columns' ) );

		add_action( 'manage_exam_posts_custom_column', array( $this, 'render_exam_columns' ), 2 );

		add_filter('post_row_actions', array( $this, 'remove_bulk_actions' ), 10, 1 );
		
		include_once( 'class-yaexam-admin-meta-boxes.php' );
	}

	public function remove_bulk_actions( $actions ) {

		if( get_post_type() == 'exam' ){

			unset( $actions['edit'] );
			unset( $actions['inline hide-if-no-js'] );
		}

        return $actions;
	}

	public function exam_columns( $existing_columns ) {
		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
			$existing_columns = array();
		}
		
		$columns          		= array();
		$columns['cb']    		= '<input type="checkbox" />';
		$columns['title']		= __( 'Title', 'yaexam' );
		$columns['duration']	= __( 'Duration', 'yaexam' );
		$columns['attempt']		= __( 'Attempt', 'yaexam' );
		$columns['exam_cat'] 	= __( 'Categories', 'yaexam' );
		$columns['result'] 		= __( 'Result', 'yaexam' );

		return array_merge( $columns, $existing_columns );
	}
	
	public function render_exam_columns( $column ) {
		global $post;

		$the_exam = yaexam_get_exam( $post );
		
		$settings	=	$the_exam->get_settings();
		
		switch ( $column ) {
			case 'exam_cat' :
				
				if ( ! $terms = get_the_terms( $post->ID, $column ) ) {
					echo '<span class="na">&ndash;</span>';
				} else {
					$termlist = array();
					foreach ( $terms as $term ) {
						$termlist[] = '<a href="' . admin_url( 'edit.php?' . $column . '=' . $term->slug . '&post_type=exam' ) . ' ">' . $term->name . '</a>';
					}

					echo implode( ', ', $termlist );
				}
				break;
			case 'duration' :
			
				echo $the_exam->get_duration();
			
				break;
			case 'attempt' :
		
				echo $the_exam->get_attempt();
		
				break;
			case 'result' :

				$link = apply_filters('yaexam_admin_render_exam_result_column', admin_url("edit.php?exam={$post->ID}&action=filter&post_type=exam&page=em-results"), $post->ID);
				
				echo '<a class="em-btn em-btn-info em-btn-sm" href="' . $link . ' ">' . esc_html__('View All', 'yaexam') . '</a>';
				
				break;
			default:
				break;
		}
	}
}

endif;

new YAEXAM_Admin_Post_Types();