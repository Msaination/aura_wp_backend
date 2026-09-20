<?php
/**
 * The template to display the widgets area in the header
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0
 */

// Header sidebar
$jacqueline_header_name    = jacqueline_get_theme_option( 'header_widgets' );
$jacqueline_header_present = ! jacqueline_is_off( $jacqueline_header_name ) && is_active_sidebar( $jacqueline_header_name );
if ( $jacqueline_header_present ) {
	jacqueline_storage_set( 'current_sidebar', 'header' );
	$jacqueline_header_wide = jacqueline_get_theme_option( 'header_wide' );
	ob_start();
	if ( is_active_sidebar( $jacqueline_header_name ) ) {
		dynamic_sidebar( $jacqueline_header_name );
	}
	$jacqueline_widgets_output = ob_get_contents();
	ob_end_clean();
	if ( ! empty( $jacqueline_widgets_output ) ) {
		$jacqueline_widgets_output = preg_replace( "/<\/aside>[\r\n\s]*<aside/", '</aside><aside', $jacqueline_widgets_output );
		$jacqueline_need_columns   = strpos( $jacqueline_widgets_output, 'columns_wrap' ) === false;
		if ( $jacqueline_need_columns ) {
			$jacqueline_columns = max( 0, (int) jacqueline_get_theme_option( 'header_columns' ) );
			if ( 0 == $jacqueline_columns ) {
				$jacqueline_columns = min( 6, max( 1, jacqueline_tags_count( $jacqueline_widgets_output, 'aside' ) ) );
			}
			if ( $jacqueline_columns > 1 ) {
				$jacqueline_widgets_output = preg_replace( '/<aside([^>]*)class="widget/', '<aside$1class="column-1_' . esc_attr( $jacqueline_columns ) . ' widget', $jacqueline_widgets_output );
			} else {
				$jacqueline_need_columns = false;
			}
		}
		?>
		<div class="header_widgets_wrap widget_area<?php echo ! empty( $jacqueline_header_wide ) ? ' header_fullwidth' : ' header_boxed'; ?>">
			<?php do_action( 'jacqueline_action_before_sidebar_wrap', 'header' ); ?>
			<div class="header_widgets_inner widget_area_inner">
				<?php
				if ( ! $jacqueline_header_wide ) {
					?>
					<div class="content_wrap">
					<?php
				}
				if ( $jacqueline_need_columns ) {
					?>
					<div class="columns_wrap">
					<?php
				}
				do_action( 'jacqueline_action_before_sidebar', 'header' );
				jacqueline_show_layout( $jacqueline_widgets_output );
				do_action( 'jacqueline_action_after_sidebar', 'header' );
				if ( $jacqueline_need_columns ) {
					?>
					</div>	<!-- /.columns_wrap -->
					<?php
				}
				if ( ! $jacqueline_header_wide ) {
					?>
					</div>	<!-- /.content_wrap -->
					<?php
				}
				?>
			</div>	<!-- /.header_widgets_inner -->
			<?php do_action( 'jacqueline_action_after_sidebar_wrap', 'header' ); ?>
		</div>	<!-- /.header_widgets_wrap -->
		<?php
	}
}
