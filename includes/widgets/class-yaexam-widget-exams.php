<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class YAEXAM_Widget_Exams extends YAEXAM_Widget {
	
	public function __construct() {
		$this->widget_cssclass    = 'yaexam widget_exams';
		$this->widget_description = __( 'Display a list of your products on your site.', 'yaexam' );
		$this->widget_id          = 'yaexam_exams';
		$this->widget_name        = __( 'Exams', 'yaexam' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => __( 'Exams', 'yaexam' ),
				'label' => __( 'Title', 'yaexam' )
			),
			'category'	=>	array(
				'std'	=>	'',
				'type'	=>	'exam_category',
				'label' => __( 'Category', 'yaexam' )
			),
			'number' => array(
				'type'  => 'number',
				'step'  => 1,
				'min'   => 1,
				'max'   => '',
				'std'   => 5,
				'label' => __( 'Number of exams to show', 'yaexam' )
			),
			'show' => array(
				'type'  => 'select',
				'std'   => '',
				'label' => __( 'Show', 'yaexam' ),
				'options' => array(
					''         => __( 'All Exams', 'yaexam' ),
					'featured' => __( 'Featured Exams', 'yaexam' ),
				)
			),
			'orderby' => array(
				'type'  => 'select',
				'std'   => 'date',
				'label' => __( 'Order by', 'yaexam' ),
				'options' => array(
					'date'   => __( 'Date', 'yaexam' ),
					'rand'   => __( 'Random', 'yaexam' ),
				)
			),
			'order' => array(
				'type'  => 'select',
				'std'   => 'desc',
				'label' => _x( 'Order', 'Sorting order', 'yaexam' ),
				'options' => array(
					'asc'  => __( 'ASC', 'yaexam' ),
					'desc' => __( 'DESC', 'yaexam' ),
				)
			),
			'display_thumbnail' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Display Thumbnail', 'yaexam' )
			),
			'display_date' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Display Date', 'yaexam' )
			),
		);
		
		parent::__construct();
	}
	
	public function get_exams( $args, $instance ) {
		$number  	= ! empty( $instance['number'] ) ? absint( $instance['number'] ) : $this->settings['number']['std'];
		$show    	= ! empty( $instance['show'] ) ? sanitize_title( $instance['show'] ) : $this->settings['show']['std'];
		$orderby 	= ! empty( $instance['orderby'] ) ? sanitize_title( $instance['orderby'] ) : $this->settings['orderby']['std'];
		$order   	= ! empty( $instance['order'] ) ? sanitize_title( $instance['order'] ) : $this->settings['order']['std'];
		$category	= ! empty( $instance['category'] ) ? absint( $instance['category'] ) : $this->settings['category']['std'];
		
		$query_args = array(
			'posts_per_page' => $number,
			'post_status'    => 'publish',
			'post_type'      => 'exam',
			'no_found_rows'  => 1,
			'order'          => $order,
			'meta_query'     => array()
		);
		
		$query_args['meta_query']   = array_filter( $query_args['meta_query'] );
		
		switch ( $show ) {
			case 'featured' :
				$query_args['meta_query'][] = array(
					'key'   => '_featured',
					'value' => 'yes'
				);
				break;
		}
		
		switch ( $orderby ) {
			case 'rand' :
				$query_args['orderby']  = 'rand';
				break;
			default :
				$query_args['orderby']  = 'date';
		}
		
		if($category) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy'	=>	'exam_cat',
					'field'		=>	'id',
					'terms'		=>	$category
				)
			);
		}
		
		return new WP_Query( apply_filters( 'yaexam_exams_widget_query_args', $query_args ) );
	}
	
	public function widget( $args, $instance ) {
		if ( $this->get_cached_widget( $args ) ) {
			return;
		}
		
		ob_start();

		if ( ( $exams = $this->get_exams( $args, $instance ) ) && $exams->have_posts() ) {

			$this->widget_start( $args, $instance );
			
			echo apply_filters( 'yaexam_before_widget_exam_list', '<div class="content"><ul class="em-ml-0 list-unstyled exam_list_widget">' );

			while ( $exams->have_posts() ) {
				$exams->the_post();
				yaexam_get_template( 'content-widget-exam.php', $instance );
			}
			
			echo apply_filters( 'yaexam_after_widget_exam_list', '</ul></div>' );

			$this->widget_end( $args );
		}
		
		wp_reset_postdata();

		echo $this->cache_widget( $args, ob_get_clean() );
	}
}