<?php
/**
 * The template to display the socials in the footer
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0.10
 */


// Socials
if ( jacqueline_is_on( jacqueline_get_theme_option( 'socials_in_footer' ) ) ) {
	$jacqueline_output = jacqueline_get_socials_links();
	if ( '' != $jacqueline_output ) {
		?>
		<div class="footer_socials_wrap socials_wrap">
			<div class="footer_socials_inner">
				<?php jacqueline_show_layout( $jacqueline_output ); ?>
			</div>
		</div>
		<?php
	}
}
