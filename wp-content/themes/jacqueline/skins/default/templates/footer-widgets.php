<?php
/**
 * The template to display the widgets area in the footer
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0.10
 */

// Footer sidebar
$jacqueline_footer_name    = jacqueline_get_theme_option( 'footer_widgets' );
$jacqueline_footer_present = ! jacqueline_is_off( $jacqueline_footer_name ) && is_active_sidebar( $jacqueline_footer_name );
if ( $jacqueline_footer_present ) {
	jacqueline_storage_set( 'current_sidebar', 'footer' );
	$jacqueline_footer_wide = jacqueline_get_theme_option( 'footer_wide' );
	ob_start();
	if ( is_active_sidebar( $jacqueline_footer_name ) ) {
		dynamic_sidebar( $jacqueline_footer_name );
	}
	$jacqueline_out = trim( ob_get_contents() );
	ob_end_clean();
	if ( ! empty( $jacqueline_out ) ) {
		$jacqueline_out          = preg_replace( "/<\\/aside>[\r\n\s]*<aside/", '</aside><aside', $jacqueline_out );
		$jacqueline_need_columns = true;   //or check: strpos($jacqueline_out, 'columns_wrap')===false;
		if ( $jacqueline_need_columns ) {
			$jacqueline_columns = max( 0, (int) jacqueline_get_theme_option( 'footer_columns' ) );			
			if ( 0 == $jacqueline_columns ) {
				$jacqueline_columns = min( 4, max( 1, jacqueline_tags_count( $jacqueline_out, 'aside' ) ) );
			}
			if ( $jacqueline_columns > 1 ) {
				$jacqueline_out = preg_replace( '/<aside([^>]*)class="widget/', '<aside$1class="column-1_' . esc_attr( $jacqueline_columns ) . ' widget', $jacqueline_out );
			} else {
				$jacqueline_need_columns = false;
			}
		}
		?>
		<div class="footer_widgets_wrap widget_area<?php echo ! empty( $jacqueline_footer_wide ) ? ' footer_fullwidth' : ''; ?> sc_layouts_row sc_layouts_row_type_normal">
			<?php do_action( 'jacqueline_action_before_sidebar_wrap', 'footer' ); ?>
			<div class="footer_widgets_inner widget_area_inner">
				<?php
				if ( ! $jacqueline_footer_wide ) {
					?>
					<div class="content_wrap">
					<?php
				}
				if ( $jacqueline_need_columns ) {
					?>
					<div class="columns_wrap">
					<?php
				}
				do_action( 'jacqueline_action_before_sidebar', 'footer' );
				jacqueline_show_layout( $jacqueline_out );
				do_action( 'jacqueline_action_after_sidebar', 'footer' );
				if ( $jacqueline_need_columns ) {
					?>
					</div><!-- /.columns_wrap -->
					<?php
				}
				if ( ! $jacqueline_footer_wide ) {
					?>
					</div><!-- /.content_wrap -->
					<?php
				}
				?>
			</div><!-- /.footer_widgets_inner -->
			<?php do_action( 'jacqueline_action_after_sidebar_wrap', 'footer' ); ?>
		</div><!-- /.footer_widgets_wrap -->
		<?php
	}
}
