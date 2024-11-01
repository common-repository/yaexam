<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


add_filter( 'body_class', 'yaexam_body_class' );

add_action( 'yaexam_before_main_content', 		'yaexam_output_content_wrapper_start', 10 );
add_action( 'yaexam_after_main_content', 		'yaexam_output_content_wrapper_end', 10 );

add_action( 'yaexam_exam_loop_item_summary', 'yaexam_template_loop_exam_thumbnail', 10 );

add_action( 'yaexam_exam_loop_item_summary', 'yaexam_template_loop_exam_wrapper_desc_start', 10 );
add_action( 'yaexam_exam_loop_item_summary', 'yaexam_template_loop_exam_category', 10 );
add_action( 'yaexam_exam_loop_item_summary', 'yaexam_template_loop_exam_title', 10 );
add_action( 'yaexam_exam_loop_item_summary', 'yaexam_template_loop_exam_info', 10 );
add_action( 'yaexam_exam_loop_item_summary', 'yaexam_template_loop_exam_excerpt', 10 );
add_action( 'yaexam_exam_loop_item_summary', 'yaexam_template_loop_exam_action', 10);
add_action( 'yaexam_exam_loop_item_summary', 'yaexam_template_loop_exam_wrapper_desc_end', 10);

add_action( 'yaexam_exam_start', 'yaexam_template_exam_start', 10);
add_action( 'yaexam_exam_doing', 'yaexam_template_exam_doing', 10);

add_action( 'yaexam_single_exam_result_summary', 'yaexam_template_single_exam_result_summary', 10);

add_action( 'yaexam_after_show_result', 'yaexam_template_remove_guest', 10 );
add_filter( 'yaexam_before_show_result', 'yaexam_template_before_show_result_is_guest', 10, 3 );


add_action( 'yaexam_single_exam_summary', 'yaexam_template_single_exam_title', 10);
add_action( 'yaexam_single_exam_summary', 'yaexam_template_single_exam_content', 10);
add_action( 'yaexam_single_exam_summary', 'yaexam_template_single_exam_start', 10);
add_action( 'yaexam_single_exam_summary', 'yaexam_template_single_exam_review', 10);

add_action( 'yaexam_single_result_info', 'yaexam_template_single_result_rating', 10, 2 );

add_filter( 'yaexam_wrap_before_nav', 'yaexam_template_filter_wrap_before_nav', 10);
add_filter( 'yaexam_wrap_after_nav', 'yaexam_template_filter_wrap_after_nav', 10);

add_action( 'yaexam_after_exam_loop', 'yaexam_pagination', 10 );

add_action( 'yaexam_before_my_account', 'yaexam_template_before_my_account', 10);
add_action( 'yaexam_after_my_account', 'yaexam_template_after_my_account', 10);

add_action( 'yaexam_after_submit_result', 'yaexam_email_result', 10 );

add_filter( 'yaexam_my_account_tabs_title', 'yaexam_template_my_account_tabs_title', 10, 1 );
add_action( 'yaexam_my_account_user_info', 'yaexam_template_my_account_user_info', 10, 1 );

add_action( 'yaexam_left_sidebar', 'yaexam_get_left_sidebar', 10 );
add_action( 'yaexam_right_sidebar', 'yaexam_get_right_sidebar', 10 );

add_filter( 'yaexam_single_exam_start_exam_info', 'yaexam_template_single_exam_start_exam_info', 10, 2 );
add_filter( 'yaexam_single_exam_data_info', 'yaexam_template_single_exam_start_data_info_adaptive', 10, 2);
add_filter( 'yaexam_single_exam_data_info', 'yaexam_template_single_exam_start_data_info_infinite', 10, 2);


add_filter( 'yaexam_can_store_result', 'yaexam_template_can_store_result', 10, 3 );

add_action( 'yaexam_before_doing', 'yaexam_template_header_doing', 10, 1 );
add_action( 'yaexam_after_doing', 'yaexam_template_navigate_doing', 10, 1 );