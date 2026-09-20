<?php
/**
 * The template to display custom header from the ThemeREX Addons Layouts
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0.06
 */

$jacqueline_header_css   = '';
$jacqueline_header_image = get_header_image();
$jacqueline_header_video = jacqueline_get_header_video();
if ( ! empty( $jacqueline_header_image ) && jacqueline_trx_addons_featured_image_override( is_singular() || jacqueline_storage_isset( 'blog_archive' ) || is_category() ) ) {
	$jacqueline_header_image = jacqueline_get_current_mode_image( $jacqueline_header_image );
}

$jacqueline_header_id = jacqueline_get_custom_header_id();
$jacqueline_header_meta = get_post_meta( $jacqueline_header_id, 'trx_addons_options', true );
if ( ! empty( $jacqueline_header_meta['margin'] ) ) {
	jacqueline_add_inline_css( sprintf( '.page_content_wrap{padding-top:%s}', esc_attr( jacqueline_prepare_css_value( $jacqueline_header_meta['margin'] ) ) ) );
}

?><header class="top_panel top_panel_custom top_panel_custom_<?php echo esc_attr( $jacqueline_header_id ); ?> top_panel_custom_<?php echo esc_attr( sanitize_title( get_the_title( $jacqueline_header_id ) ) ); ?>
				<?php
				echo ! empty( $jacqueline_header_image ) || ! empty( $jacqueline_header_video )
					? ' with_bg_image'
					: ' without_bg_image';
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

	// Custom header's layout
	do_action( 'jacqueline_action_show_layout', $jacqueline_header_id );

	// Header widgets area
	get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/header-widgets' ) );

	?>
</header>
