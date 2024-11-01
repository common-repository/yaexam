<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function yaexam_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
	if ( ! $permalink )
		$permalink = get_permalink();

	// Map endpoint to options
	$endpoint = ! empty( YAEXAM()->query->query_vars[ $endpoint ] ) ? YAEXAM()->query->query_vars[ $endpoint ] : $endpoint;

	if ( get_option( 'permalink_structure' ) ) {
		if ( strstr( $permalink, '?' ) ) {
			$query_string = '?' . parse_url( $permalink, PHP_URL_QUERY );
			$permalink    = current( explode( '?', $permalink ) );
		} else {
			$query_string = '';
		}
		$url = trailingslashit( $permalink ) . $endpoint . '/' . $value . $query_string;
	} else {
		$url = add_query_arg( $endpoint, $value, $permalink );
	}
	
	return apply_filters( 'yaexam_get_endpoint_url', $url, $endpoint, $value, $permalink );
}

function yaexam_get_page_id( $page ) {
	
	$page = apply_filters( 'yaexam_get_' . $page . '_page_id', get_option('yaexam_' . $page . '_page_id' ) );
	
	return $page ? absint( $page ) : -1;
}

function yaexam_get_page_permalink( $page ) {
	
	$page_id   = yaexam_get_page_id( $page );
	
	$permalink = $page_id ? get_permalink( $page_id ) : get_home_url();
	
	return apply_filters( 'yaexam_get_' . $page . '_page_permalink', $permalink );
}


function yaexam_nav_menu_ityaexam_classes( $menu_items ) {

	if ( ! is_yaexam() ) {
		return $menu_items;
	}

	$exam_page 		= (int) yaexam_get_page_id('archive_exam');
	$page_for_posts = (int) get_option( 'page_for_posts' );

	foreach ( (array) $menu_items as $key => $menu_item ) {

		$classes = (array) $menu_item->classes;

		// Unset active class for blog page
		if ( $page_for_posts == $menu_item->object_id ) {
			$menu_items[$key]->current = false;

			if ( in_array( 'current_page_parent', $classes ) ) {
				unset( $classes[ array_search('current_page_parent', $classes) ] );
			}

			if ( in_array( 'current-menu-item', $classes ) ) {
				unset( $classes[ array_search('current-menu-item', $classes) ] );
			}

		// Set active state if this is the shop page link
		} elseif ( is_exam() && $exam_page == $menu_item->object_id && 'page' === $menu_item->object ) {
			$menu_items[ $key ]->current = true;
			$classes[] = 'current-menu-item';
			$classes[] = 'current_page_item';

		// Set parent state if this is a product page
		} elseif ( is_singular( 'exam' ) && $exam_page == $menu_item->object_id ) {
			$classes[] = 'current_page_parent';
		}

		$menu_items[ $key ]->classes = array_unique( $classes );

	}

	return $menu_items;
}
add_filter( 'wp_nav_menu_objects', 'yaexam_nav_menu_ityaexam_classes', 2 );

function yaexam_list_pages( $pages ) {
    if (is_yaexam()) {
        $pages = str_replace( 'current_page_parent', '', $pages); // remove current_page_parent class from any item
        $exam_page = 'page-item-' . yaexam_get_page_id('archive_exam'); // find shop_page_id through woocommerce options

        if (is_exam()) :
        	$pages = str_replace($exam_page, $exam_page . ' current_page_item', $pages); // add current_page_item class to shop page
    	else :
    		$pages = str_replace($exam_page, $exam_page . ' current_page_parent', $pages); // add current_page_parent class to shop page
    	endif;
    }
    return $pages;
}
add_filter( 'wp_list_pages', 'yaexam_list_pages' );