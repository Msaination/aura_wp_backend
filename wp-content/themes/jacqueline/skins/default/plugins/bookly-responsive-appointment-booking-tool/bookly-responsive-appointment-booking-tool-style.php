<?php
// Add plugin-specific colors and fonts to the custom CSS
if ( ! function_exists( 'jacqueline_bookly_responsive_appointment_booking_tool_get_css' ) ) {
	add_filter( 'jacqueline_filter_get_css', 'jacqueline_bookly_responsive_appointment_booking_tool_get_css', 10, 2 );
	function jacqueline_bookly_responsive_appointment_booking_tool_get_css( $css, $args ) {

		if ( isset( $css['fonts'] ) && isset( $args['fonts'] ) ) {
			$fonts         = $args['fonts'];
			$css['fonts'] .= <<<CSS

.bookly-form .picker__button--today,
.bookly-form .picker__button--clear,
.bookly-form .bookly-btn,
.bookly-form .bookly-btn > span {
    {$fonts['button_font-family']}
}
.bookly-form .picker__header,
.bookly-form .picker__weekday {
    {$fonts['h5_font-family']}
}
CSS;
		}

		return $css;
	}
}

