<?php
/**
 * The template 'Style 2' to displaying related posts
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0
 */

$jacqueline_link        = get_permalink();
$jacqueline_post_format = get_post_format();
$jacqueline_post_format = empty( $jacqueline_post_format ) ? 'standard' : str_replace( 'post-format-', '', $jacqueline_post_format );
?><div id="post-<?php the_ID(); ?>" <?php post_class( 'related_item post_format_' . esc_attr( $jacqueline_post_format ) ); ?> data-post-id="<?php the_ID(); ?>">
	<?php
	jacqueline_show_post_featured(
		array(
			'thumb_ratio'   => '300:223',
			'thumb_size'    => apply_filters( 'jacqueline_filter_related_thumb_size', jacqueline_get_thumb_size( (int) jacqueline_get_theme_option( 'related_posts' ) == 1 ? 'huge' : 'square' ) ),
		)
	);
	?>
	<div class="post_header entry-header">
		<?php
		if ( in_array( get_post_type(), array( 'post', 'attachment' ) ) ) {

			jacqueline_show_post_meta(
				array(
					'components' => 'categories',
					'class'      => 'post_meta_categories',
				)
			);

		}
		?>
		<h6 class="post_title entry-title"><a href="<?php echo esc_url( $jacqueline_link ); ?>"><?php
			if ( '' == get_the_title() ) {
				esc_html_e( '- No title -', 'jacqueline' );
			} else {
				the_title();
			}
		?></a></h6>
	</div>
</div>
