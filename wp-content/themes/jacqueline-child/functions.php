<?php
/**
 * Child-Theme functions and definitions
 */

// Load rtl.css because it is not autoloaded from the child theme
if ( ! function_exists( 'jacqueline_child_load_rtl' ) ) {
	add_filter( 'wp_enqueue_scripts', 'jacqueline_child_load_rtl', 3000 );
	function jacqueline_child_load_rtl() {
		if ( is_rtl() ) {
			wp_enqueue_style( 'jacqueline-style-rtl', get_template_directory_uri() . '/rtl.css' );
		}
	}
}

if ( ! function_exists( 'jacqueline_child_add_fullwidth_body_style' ) ) {
	add_filter( 'jacqueline_filter_list_body_styles', 'jacqueline_child_add_fullwidth_body_style' );
	function jacqueline_child_add_fullwidth_body_style( $styles ) {
		$styles['fullwide'] = array(
			'title' => esc_html__( 'Full width (no margins)', 'jacqueline' ),
			'icon'  => 'images/theme-options/body-style/fullwide.png',
		);

		return $styles;
	}
}
