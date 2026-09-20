<?php
/**
 * The custom template to display the content
 *
 * Used for index/archive/search.
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0.50
 */

$jacqueline_template_args = get_query_var( 'jacqueline_template_args' );
if ( is_array( $jacqueline_template_args ) ) {
	$jacqueline_columns    = empty( $jacqueline_template_args['columns'] ) ? 2 : max( 1, $jacqueline_template_args['columns'] );
	$jacqueline_blog_style = array( $jacqueline_template_args['type'], $jacqueline_columns );
} else {
	$jacqueline_template_args = array();
	$jacqueline_blog_style = explode( '_', jacqueline_get_theme_option( 'blog_style' ) );
	$jacqueline_columns    = empty( $jacqueline_blog_style[1] ) ? 2 : max( 1, $jacqueline_blog_style[1] );
}
$jacqueline_blog_id       = jacqueline_get_custom_blog_id( join( '_', $jacqueline_blog_style ) );
$jacqueline_blog_style[0] = str_replace( 'blog-custom-', '', $jacqueline_blog_style[0] );
$jacqueline_expanded      = ! jacqueline_sidebar_present() && jacqueline_get_theme_option( 'expand_content' ) == 'expand';
$jacqueline_components    = ! empty( $jacqueline_template_args['meta_parts'] )
							? ( is_array( $jacqueline_template_args['meta_parts'] )
								? join( ',', $jacqueline_template_args['meta_parts'] )
								: $jacqueline_template_args['meta_parts']
								)
							: jacqueline_array_get_keys_by_value( jacqueline_get_theme_option( 'meta_parts' ) );
$jacqueline_post_format   = get_post_format();
$jacqueline_post_format   = empty( $jacqueline_post_format ) ? 'standard' : str_replace( 'post-format-', '', $jacqueline_post_format );

$jacqueline_blog_meta     = jacqueline_get_custom_layout_meta( $jacqueline_blog_id );
$jacqueline_custom_style  = ! empty( $jacqueline_blog_meta['scripts_required'] ) ? $jacqueline_blog_meta['scripts_required'] : 'none';

if ( ! empty( $jacqueline_template_args['slider'] ) || $jacqueline_columns > 1 || ! jacqueline_is_off( $jacqueline_custom_style ) ) {
	?><div class="
		<?php
		if ( ! empty( $jacqueline_template_args['slider'] ) ) {
			echo 'slider-slide swiper-slide';
		} else {
			echo esc_attr( ( jacqueline_is_off( $jacqueline_custom_style ) ? 'column' : sprintf( '%1$s_item %1$s_item', $jacqueline_custom_style ) ) . "-1_{$jacqueline_columns}" );
		}
		?>
	">
	<?php
}
?>
<article id="post-<?php the_ID(); ?>" data-post-id="<?php the_ID(); ?>"
	<?php
	post_class(
			'post_item post_item_container post_format_' . esc_attr( $jacqueline_post_format )
					. ' post_layout_custom post_layout_custom_' . esc_attr( $jacqueline_columns )
					. ' post_layout_' . esc_attr( $jacqueline_blog_style[0] )
					. ' post_layout_' . esc_attr( $jacqueline_blog_style[0] ) . '_' . esc_attr( $jacqueline_columns )
					. ( ! jacqueline_is_off( $jacqueline_custom_style )
						? ' post_layout_' . esc_attr( $jacqueline_custom_style )
							. ' post_layout_' . esc_attr( $jacqueline_custom_style ) . '_' . esc_attr( $jacqueline_columns )
						: ''
						)
		);
	jacqueline_add_blog_animation( $jacqueline_template_args );
	?>
>
	<?php
	// Sticky label
	if ( is_sticky() && ! is_paged() ) {
		?>
		<span class="post_label label_sticky"></span>
		<?php
	}
	// Custom layout
	do_action( 'jacqueline_action_show_layout', $jacqueline_blog_id, get_the_ID() );
	?>
</article><?php
if ( ! empty( $jacqueline_template_args['slider'] ) || $jacqueline_columns > 1 || ! jacqueline_is_off( $jacqueline_custom_style ) ) {
	?></div><?php
	// Need opening PHP-tag above just after </div>, because <div> is a inline-block element (used as column)!
}
