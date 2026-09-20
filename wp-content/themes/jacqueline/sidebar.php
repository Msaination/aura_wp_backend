<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0
 */

if ( jacqueline_sidebar_present() ) {
	
	$jacqueline_sidebar_type = jacqueline_get_theme_option( 'sidebar_type' );
	if ( 'custom' == $jacqueline_sidebar_type && ! jacqueline_is_layouts_available() ) {
		$jacqueline_sidebar_type = 'default';
	}
	
	// Catch output to the buffer
	ob_start();
	if ( 'default' == $jacqueline_sidebar_type ) {
		// Default sidebar with widgets
		$jacqueline_sidebar_name = jacqueline_get_theme_option( 'sidebar_widgets' );
		jacqueline_storage_set( 'current_sidebar', 'sidebar' );
		if ( is_active_sidebar( $jacqueline_sidebar_name ) ) {
			dynamic_sidebar( $jacqueline_sidebar_name );
		}
	} else {
		// Custom sidebar from Layouts Builder
		$jacqueline_sidebar_id = jacqueline_get_custom_sidebar_id();
		do_action( 'jacqueline_action_show_layout', $jacqueline_sidebar_id );
	}
	$jacqueline_out = trim( ob_get_contents() );
	ob_end_clean();
	
	// If any html is present - display it
	if ( ! empty( $jacqueline_out ) ) {
		$jacqueline_sidebar_position    = jacqueline_get_theme_option( 'sidebar_position' );
		$jacqueline_sidebar_position_ss = jacqueline_get_theme_option( 'sidebar_position_ss', 'below' );
		?>
		<div class="sidebar widget_area
			<?php
			echo ' ' . esc_attr( $jacqueline_sidebar_position );
			echo ' sidebar_' . esc_attr( $jacqueline_sidebar_position_ss );
			echo ' sidebar_' . esc_attr( $jacqueline_sidebar_type );

			$jacqueline_sidebar_scheme = apply_filters( 'jacqueline_filter_sidebar_scheme', jacqueline_get_theme_option( 'sidebar_scheme', 'inherit' ) );
			if ( ! empty( $jacqueline_sidebar_scheme ) && ! jacqueline_is_inherit( $jacqueline_sidebar_scheme ) && 'custom' != $jacqueline_sidebar_type ) {
				echo ' scheme_' . esc_attr( $jacqueline_sidebar_scheme );
			}
			?>
		" role="complementary">
			<?php

			// Skip link anchor to fast access to the sidebar from keyboard
			?>
			<span id="sidebar_skip_link_anchor" class="jacqueline_skip_link_anchor"></span>
			<?php

			do_action( 'jacqueline_action_before_sidebar_wrap', 'sidebar' );

			// Button to show/hide sidebar on mobile
			if ( in_array( $jacqueline_sidebar_position_ss, array( 'above', 'float' ) ) ) {
				$jacqueline_title = apply_filters( 'jacqueline_filter_sidebar_control_title', 'float' == $jacqueline_sidebar_position_ss ? esc_html__( 'Show Sidebar', 'jacqueline' ) : '' );
				$jacqueline_text  = apply_filters( 'jacqueline_filter_sidebar_control_text', 'above' == $jacqueline_sidebar_position_ss ? esc_html__( 'Show Sidebar', 'jacqueline' ) : '' );
				?>
				<a href="#" role="button" class="sidebar_control" title="<?php echo esc_attr( $jacqueline_title ); ?>"><?php echo esc_html( $jacqueline_text ); ?></a>
				<?php
			}
			?>
			<div class="sidebar_inner">
				<?php
				do_action( 'jacqueline_action_before_sidebar', 'sidebar' );
				jacqueline_show_layout( preg_replace( "/<\/aside>[\r\n\s]*<aside/", '</aside><aside', $jacqueline_out ) );
				do_action( 'jacqueline_action_after_sidebar', 'sidebar' );
				?>
			</div>
			<?php

			do_action( 'jacqueline_action_after_sidebar_wrap', 'sidebar' );

			?>
		</div>
		<div class="clearfix"></div>
		<?php
	}
}
