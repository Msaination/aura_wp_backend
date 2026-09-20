<?php
/**
 * The template to display default site header
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0
 */

$jacqueline_header_css   = '';
$jacqueline_header_image = get_header_image();
$jacqueline_header_video = jacqueline_get_header_video();
if ( ! empty( $jacqueline_header_image ) && jacqueline_trx_addons_featured_image_override( is_singular() || jacqueline_storage_isset( 'blog_archive' ) || is_category() ) ) {
	$jacqueline_header_image = jacqueline_get_current_mode_image( $jacqueline_header_image );
}

?><header class="top_panel top_panel_default
	<?php
	echo ! empty( $jacqueline_header_image ) || ! empty( $jacqueline_header_video ) ? ' with_bg_image' : ' without_bg_image';
	if ( '' != $jacqueline_header_video ) {
		echo ' with_bg_video';
	}
	if ( '' != $jacqueline_header_image ) {
		echo ' ' . esc_attr( jacqueline_add_inline_css_class( 'background-image: url(' . esc_url( $jacqueline_header_image ) . ');' ) );
	}
	if ( is_single() && has_post_thumbnail() ) {
		echo ' with_featured_image';
	}
	if ( jacqueline_is_on( jacqueline_get_theme_option( 'header_fullheight' ) ) ) {
		echo ' header_fullheight jacqueline-full-height';
	}
	$jacqueline_header_scheme = jacqueline_get_theme_option( 'header_scheme' );
	if ( ! empty( $jacqueline_header_scheme ) && ! jacqueline_is_inherit( $jacqueline_header_scheme  ) ) {
		echo ' scheme_' . esc_attr( $jacqueline_header_scheme );
	}
	?>
">
	<?php

	// Background video
	if ( ! empty( $jacqueline_header_video ) ) {
		get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/header-video' ) );
	}

	// Main menu
	get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/header-navi' ) );

	// Mobile header
	if ( jacqueline_is_on( jacqueline_get_theme_option( 'header_mobile_enabled' ) ) ) {
		get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/header-mobile' ) );
	}

	// Page title and breadcrumbs area
	if ( ! is_single() ) {
		get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/header-title' ) );
	}

	// Header widgets area
	get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/header-widgets' ) );
	?>
</header>
