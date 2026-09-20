<?php
/**
 * The template to display the site logo in the footer
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0.10
 */

// Logo
if ( jacqueline_is_on( jacqueline_get_theme_option( 'logo_in_footer' ) ) ) {
	$jacqueline_logo_image = jacqueline_get_logo_image( 'footer' );
	$jacqueline_logo_text  = get_bloginfo( 'name' );
	if ( ! empty( $jacqueline_logo_image['logo'] ) || ! empty( $jacqueline_logo_text ) ) {
		?>
		<div class="footer_logo_wrap">
			<div class="footer_logo_inner">
				<?php
				if ( ! empty( $jacqueline_logo_image['logo'] ) ) {
					$jacqueline_attr = jacqueline_getimagesize( $jacqueline_logo_image['logo'] );
					echo '<a href="' . esc_url( home_url( '/' ) ) . '">'
							. '<img src="' . esc_url( $jacqueline_logo_image['logo'] ) . '"'
								. ( ! empty( $jacqueline_logo_image['logo_retina'] ) ? ' srcset="' . esc_url( $jacqueline_logo_image['logo_retina'] ) . ' 2x"' : '' )
								. ' class="logo_footer_image"'
								. ' alt="' . esc_attr__( 'Site logo', 'jacqueline' ) . '"'
								. ( ! empty( $jacqueline_attr[3] ) ? ' ' . wp_kses_data( $jacqueline_attr[3] ) : '' )
							. '>'
						. '</a>';
				} elseif ( ! empty( $jacqueline_logo_text ) ) {
					echo '<h1 class="logo_footer_text">'
							. '<a href="' . esc_url( home_url( '/' ) ) . '">'
								. esc_html( $jacqueline_logo_text )
							. '</a>'
						. '</h1>';
				}
				?>
			</div>
		</div>
		<?php
	}
}
