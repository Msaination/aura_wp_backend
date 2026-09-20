<?php
/**
 * The template to display the copyright info in the footer
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0.10
 */

// Copyright area
?> 
<div class="footer_copyright_wrap
<?php
$jacqueline_copyright_scheme = jacqueline_get_theme_option( 'copyright_scheme' );
if ( ! empty( $jacqueline_copyright_scheme ) && ! jacqueline_is_inherit( $jacqueline_copyright_scheme  ) ) {
	echo ' scheme_' . esc_attr( $jacqueline_copyright_scheme );
}
?>
				">
	<div class="footer_copyright_inner">
		<div class="content_wrap">
			<div class="copyright_text">
			<?php
				$jacqueline_copyright = jacqueline_get_theme_option( 'copyright' );
			if ( ! empty( $jacqueline_copyright ) ) {
				// Replace {{Y}} or {Y} with the current year
				$jacqueline_copyright = str_replace( array( '{{Y}}', '{Y}' ), date( 'Y' ), $jacqueline_copyright );
				// Replace {{...}} and ((...)) on the <i>...</i> and <b>...</b>
				$jacqueline_copyright = jacqueline_prepare_macros( $jacqueline_copyright );
				// Display copyright
				echo wp_kses( nl2br( $jacqueline_copyright ), 'jacqueline_kses_content' );
			}
			?>
			</div>
		</div>
	</div>
</div>
