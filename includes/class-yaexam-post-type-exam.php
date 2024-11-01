<?php

namespace YaExam;

defined( 'ABSPATH' ) || exit;

/**
 * YAEXAM_Post_Type_Exam Class.
 */
class YAEXAM_Post_Type_Exam {
	
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 6 );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 6 );
		add_action( 'init', array( __CLASS__, 'support_jetpack_omnisearch' ) );
		add_filter( 'rest_api_allowed_post_types', array( __CLASS__, 'rest_api_allowed_post_types' ) );

		self::rest_api();
	}
	
	public static function register_taxonomies() {
		if ( taxonomy_exists( 'exam_type' ) ) {
			return;
		}

		do_action( 'yaexam_register_taxonomy' );

		$permalinks = get_option( 'yaexam_permalinks' );

		register_taxonomy( 'exam_type',
			apply_filters( 'yaexam_taxonomy_objects_exam_type', array( 'exam' ) ),
			apply_filters( 'yaexam_taxonomy_args_exam_type', array(
				'hierarchical'      => false,
				'show_ui'           => false,
				'show_in_nav_menus' => false,
				'query_var'         => is_admin(),
				'rewrite'           => false,
				'public'            => false,
			) )
		);
		
		register_taxonomy( 'exam_cat',
			apply_filters( 'yaexam_taxonomy_objects_exam_cat', array( 'exam' ) ),
			apply_filters( 'yaexam_taxonomy_args_exam_cat', array(
				'hierarchical'          => true,
				'label'                 => __( 'Exam Categories', 'yaexam' ),
				'labels' => array(
						'name'              => __( 'Exam Categories', 'yaexam' ),
						'singular_name'     => __( 'Exam Category', 'yaexam' ),
						'menu_name'         => _x( 'Categories', 'Admin menu name', 'yaexam' ),
						'search_items'      => __( 'Search Exam Categories', 'yaexam' ),
						'all_items'         => __( 'All Exam Categories', 'yaexam' ),
						'parent_item'       => __( 'Parent Exam Category', 'yaexam' ),
						'edit_item'         => __( 'Edit Exam Category', 'yaexam' ),
						'update_item'       => __( 'Update Exam Category', 'yaexam' ),
						'add_new_item'      => __( 'Add New Exam Category', 'yaexam' ),
					),
				'show_ui'               => true,
				'query_var'             => true,
				'capabilities'          => array(
					'manage_terms' => 'manage_exam_terms',
					'edit_terms'   => 'edit_exam_terms',
					'delete_terms' => 'delete_exam_terms',
					'assign_terms' => 'assign_exam_terms'
				),
				'show_in_rest' 			=> true,
				'rewrite'               => array(
					'slug'         => empty( $permalinks['category_base'] ) ? _x( 'exam-category', 'slug', 'yaexam' ) : $permalinks['category_base'],
					'with_front'   => false,
					'hierarchical' => true,
				),
			) )
		);

		register_taxonomy( 'exam_tag',
			apply_filters( 'yaexam_taxonomy_objects_exam_tag', array( 'exam' ) ),
			apply_filters( 'yaexam_taxonomy_args_exam_tag', array(
				'hierarchical'          => false,
				'update_count_callback' => '_wc_term_recount',
				'label'                 => __( 'Exam Tags', 'yaexam' ),
				'labels'                => array(
						'name'                       => __( 'Exam Tags', 'yaexam' ),
						'singular_name'              => __( 'Exam Tag', 'yaexam' ),
						'menu_name'                  => _x( 'Tags', 'Admin menu name', 'yaexam' ),
						'search_items'               => __( 'Search Exam Tags', 'yaexam' ),
						'all_items'                  => __( 'All Exam Tags', 'yaexam' ),
						'edit_item'                  => __( 'Edit Exam Tag', 'yaexam' ),
						'update_item'                => __( 'Update Exam Tag', 'yaexam' ),
						'add_new_item'               => __( 'Add New Exam Tag', 'yaexam' ),
						'popular_items'              => __( 'Popular Exam Tags', 'yaexam' ),
						'separate_items_with_commas' => __( 'Separate Exam Tags with commas', 'yaexam'  ),
						'add_or_remove_items'        => __( 'Add or remove Exam Tags', 'yaexam' ),
						'choose_from_most_used'      => __( 'Choose from the most used Exam tags', 'yaexam' ),
						'not_found'                  => __( 'No Exam Tags found', 'yaexam' ),
					),
				'show_ui'               => true,
				'query_var'             => true,
				'capabilities'          => array(
					'manage_terms' => 'manage_exam_terms',
					'edit_terms'   => 'edit_exam_terms',
					'delete_terms' => 'delete_exam_terms',
					'assign_terms' => 'assign_exam_terms',
				),
				'rewrite'               => array(
					'slug'       => empty( $permalinks['tag_base'] ) ? _x( 'exam-tag', 'slug', 'yaexam' ) : $permalinks['tag_base'],
					'with_front' => false
				),
			) )
		);



		do_action( 'yaexam_after_register_taxonomy' );
	}

	public static function rest_api()
    {
        add_action( 'rest_api_init', function(){

            register_rest_field( 'exam',
                'metadata',
                array(
                    'update_callback' => function( $field_value, $data ){
                        if ( is_array( $field_value ) ) {
                            foreach ( $field_value as $key => $value ) {
                                update_post_meta( $data->ID, $key, $value );
                            }
                            return true;
                        }
                    },
                    'schema'  => null,
                )
            );
        });
    }
	
	public static function register_post_types() {
		if ( post_type_exists('exam') ) {
			return;
		}
		
		do_action( 'yaexam_register_post_type' );

		$archive_exam_page_id	=	yaexam_get_page_id( 'archive_exam' );
		
		register_post_type( 'exam',
			apply_filters( 'yaexam_register_post_type_exam',
				array(
					'labels'              => array(
							'name'                  => __( 'Exams', 'yaexam' ),
							'singular_name'         => __( 'Exam', 'yaexam' ),
							'menu_name'             => _x( 'Exams', 'Admin menu name', 'yaexam' ),
							'add_new'               => __( 'Add Exam', 'yaexam' ),
							'add_new_item'          => __( 'Add New Exam', 'yaexam' ),
							'edit'                  => __( 'Edit', 'yaexam' ),
							'edit_item'             => __( 'Edit Exam', 'yaexam' ),
							'new_item'              => __( 'New Exam', 'yaexam' ),
							'view'                  => __( 'View Exam', 'yaexam' ),
							'view_item'             => __( 'View Exam', 'yaexam' ),
							'search_items'          => __( 'Search Exams', 'yaexam' ),
							'not_found'             => __( 'No Exams found', 'yaexam' ),
							'not_found_in_trash'    => __( 'No Exams found in trash', 'yaexam' ),
							'parent'                => __( 'Parent Exam', 'yaexam' ),
							'featured_image'        => __( 'Exam Image', 'yaexam' ),
							'set_featured_image'    => __( 'Set exam image', 'yaexam' ),
							'remove_featured_image' => __( 'Remove exam image', 'yaexam' ),
							'use_featured_image'    => __( 'Use as exam image', 'yaexam' ),
						),
					'description'         => __( 'This is where you can add new exams.', 'yaexam' ),
					// 'menu_icon'			  => 'dashicons-yaexam-exams',
					'public'              => true,
					'show_ui'             => true,
					'map_meta_cap'        => true,
					'publicly_queryable'  => true,
					'exclude_from_search' => false,
					'hierarchical'        => false,
					'rewrite'             => array( 'slug' => untrailingslashit( 'exam' ), 'with_front' => false, 'feeds' => true ),
					'query_var'           => true,
					'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'page-attributes', 'publicize' ),
					'has_archive'         => $archive_exam_page_id && get_post( $archive_exam_page_id ) ? get_page_uri( $archive_exam_page_id ) : 'exams',
					'taxonomies'		  => array('exam_cat'),
					'show_in_nav_menus'   => true,
					'show_in_rest' 			=> true,
					'capability_type' 		=> 'exam',
					'rest_base'             => 'exam',
					'rest_controller_class' => 'WP_REST_Posts_Controller',
				)
			)
		);
	}
		
	public static function support_jetpack_omnisearch() {
		if ( class_exists( 'Jetpack_Omnisearch_Posts' ) ) {
			new Jetpack_Omnisearch_Posts( 'exam' );
		}
	}
	
	public static function rest_api_allowed_post_types( $post_types ) {
		$post_types[] = 'exam';

		return $post_types;
	}
}

YAEXAM_Post_Type_Exam::init();
