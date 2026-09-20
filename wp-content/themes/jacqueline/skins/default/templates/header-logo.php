<?php
/**
 * The template to display the logo or the site name and the slogan in the Header
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0
 */

$jacqueline_args = get_query_var( 'jacqueline_logo_args' );

// Site logo
$jacqueline_logo_type   = isset( $jacqueline_args['type'] ) ? $jacqueline_args['type'] : '';
$jacqueline_logo_image  = jacqueline_get_logo_image( $jacqueline_logo_type );
$jacqueline_logo_text   = jacqueline_is_on( jacqueline_get_theme_option( 'logo_text' ) ) ? get_bloginfo( 'name' ) : '';
$jacqueline_logo_slogan = get_bloginfo( 'description', 'display' );
if ( ! empty( $jacqueline_logo_image['logo'] ) || ! empty( $jacqueline_logo_text ) ) {
	?><a class="sc_layouts_logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php
		if ( ! empty( $jacqueline_logo_image['logo'] ) ) {
			if ( empty( $jacqueline_logo_type ) && function_exists( 'the_custom_logo' ) && is_numeric($jacqueline_logo_image['logo']) && (int) $jacqueline_logo_image['logo'] > 0 ) {
				the_custom_logo();
			} else {
				$jacqueline_attr = jacqueline_getimagesize( $jacqueline_logo_image['logo'] );
				echo '<img src="' . esc_url( $jacqueline_logo_image['logo'] ) . '"'
						. ( ! empty( $jacqueline_logo_image['logo_retina'] ) ? ' srcset="' . esc_url( $jacqueline_logo_image['logo_retina'] ) . ' 2x"' : '' )
						. ' alt="' . esc_attr( $jacqueline_logo_text ) . '"'
						. ( ! empty( $jacqueline_attr[3] ) ? ' ' . wp_kses_data( $jacqueline_attr[3] ) : '' )
						. '>';
			}
		} else {
			jacqueline_show_layout( jacqueline_prepare_macros( $jacqueline_logo_text ), '<span class="logo_text">', '</span>' );
			jacqueline_show_layout( jacqueline_prepare_macros( $jacqueline_logo_slogan ), '<span class="logo_slogan">', '</span>' );
		}
		?>
	</a>
	<?php
}
