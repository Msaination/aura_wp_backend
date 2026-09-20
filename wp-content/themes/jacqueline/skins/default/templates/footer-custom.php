<?php
/**
 * The template to display default site footer
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0.10
 */

$jacqueline_footer_id = jacqueline_get_custom_footer_id();
$jacqueline_footer_meta = get_post_meta( $jacqueline_footer_id, 'trx_addons_options', true );
if ( ! empty( $jacqueline_footer_meta['margin'] ) ) {
	jacqueline_add_inline_css( sprintf( '.page_content_wrap{padding-bottom:%s}', esc_attr( jacqueline_prepare_css_value( $jacqueline_footer_meta['margin'] ) ) ) );
}
?>
<footer class="footer_wrap footer_custom footer_custom_<?php echo esc_attr( $jacqueline_footer_id ); ?> footer_custom_<?php echo esc_attr( sanitize_title( get_the_title( $jacqueline_footer_id ) ) ); ?>
						<?php
						$jacqueline_footer_scheme = jacqueline_get_theme_option( 'footer_scheme' );
						if ( ! empty( $jacqueline_footer_scheme ) && ! jacqueline_is_inherit( $jacqueline_footer_scheme  ) ) {
							echo ' scheme_' . esc_attr( $jacqueline_footer_scheme );
						}
						?>
						">
	<?php
	// Custom footer's layout
	do_action( 'jacqueline_action_show_layout', $jacqueline_footer_id );
	?>
</footer><!-- /.footer_wrap -->
