<?php
/**
 * The template to display the background video in the header
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0.14
 */
$jacqueline_header_video = jacqueline_get_header_video();
$jacqueline_embed_video  = '';
if ( ! empty( $jacqueline_header_video ) && ! jacqueline_is_from_uploads( $jacqueline_header_video ) ) {
	if ( jacqueline_is_youtube_url( $jacqueline_header_video ) && preg_match( '/[=\/]([^=\/]*)$/', $jacqueline_header_video, $matches ) && ! empty( $matches[1] ) ) {
		?><div id="background_video" data-youtube-code="<?php echo esc_attr( $matches[1] ); ?>"></div>
		<?php
	} else {
		?>
		<div id="background_video"><?php jacqueline_show_layout( jacqueline_get_embed_video( $jacqueline_header_video ) ); ?></div>
		<?php
	}
}
