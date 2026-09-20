<?php
/**
 * The template to display default site footer
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0.10
 */

?>
<footer class="footer_wrap footer_default
<?php
$jacqueline_footer_scheme = jacqueline_get_theme_option( 'footer_scheme' );
if ( ! empty( $jacqueline_footer_scheme ) && ! jacqueline_is_inherit( $jacqueline_footer_scheme  ) ) {
	echo ' scheme_' . esc_attr( $jacqueline_footer_scheme );
}
?>
				">
	<?php

	// Footer widgets area
	get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/footer-widgets' ) );

	// Logo
	get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/footer-logo' ) );

	// Socials
	get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/footer-socials' ) );

	// Copyright area
	get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/footer-copyright' ) );

	?>
</footer><!-- /.footer_wrap -->
