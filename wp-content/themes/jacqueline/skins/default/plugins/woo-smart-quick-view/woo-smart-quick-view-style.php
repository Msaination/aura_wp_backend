<?php
// Add plugin-specific colors and fonts to the custom CSS
if ( ! function_exists( 'jacqueline_quick_view_get_css' ) ) {
	add_filter( 'jacqueline_filter_get_css', 'jacqueline_quick_view_get_css', 10, 2 );
	function jacqueline_quick_view_get_css( $css, $args ) {
		if ( isset( $css['fonts'] ) && isset( $args['fonts'] ) ) {
			$fonts         = $args['fonts'];
			$css['fonts'] .= <<<CSS

		.woosq-sidebar {
			{$fonts['p_font-family']}
		}
		.woosq-btn {
			{$fonts['button_font-family']}
		}

CSS;
		}

		return $css;
	}
}

