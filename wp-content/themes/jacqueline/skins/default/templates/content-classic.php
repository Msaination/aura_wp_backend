<?php
/**
 * The Classic template to display the content
 *
 * Used for index/archive/search.
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0
 */

$jacqueline_template_args = get_query_var( 'jacqueline_template_args' );

if ( is_array( $jacqueline_template_args ) ) {
	$jacqueline_columns    = empty( $jacqueline_template_args['columns'] ) ? 2 : max( 1, $jacqueline_template_args['columns'] );
	$jacqueline_blog_style = array( $jacqueline_template_args['type'], $jacqueline_columns );
    $jacqueline_columns_class = jacqueline_get_column_class( 1, $jacqueline_columns, ! empty( $jacqueline_template_args['columns_tablet']) ? $jacqueline_template_args['columns_tablet'] : '', ! empty($jacqueline_template_args['columns_mobile']) ? $jacqueline_template_args['columns_mobile'] : '' );
} else {
	$jacqueline_template_args = array();
	$jacqueline_blog_style = explode( '_', jacqueline_get_theme_option( 'blog_style' ) );
	$jacqueline_columns    = empty( $jacqueline_blog_style[1] ) ? 2 : max( 1, $jacqueline_blog_style[1] );
    $jacqueline_columns_class = jacqueline_get_column_class( 1, $jacqueline_columns );
}
$jacqueline_expanded   = ! jacqueline_sidebar_present() && jacqueline_get_theme_option( 'expand_content' ) == 'expand';

$jacqueline_post_format = get_post_format();
$jacqueline_post_format = empty( $jacqueline_post_format ) ? 'standard' : str_replace( 'post-format-', '', $jacqueline_post_format );

?><div class="<?php
	if ( ! empty( $jacqueline_template_args['slider'] ) ) {
		echo ' slider-slide swiper-slide';
	} else {
		echo ( jacqueline_is_blog_style_use_masonry( $jacqueline_blog_style[0] ) ? 'masonry_item masonry_item-1_' . esc_attr( $jacqueline_columns ) : esc_attr( $jacqueline_columns_class ) );
	}
?>"><article id="post-<?php the_ID(); ?>" data-post-id="<?php the_ID(); ?>"
	<?php
	post_class(
		'post_item post_item_container post_format_' . esc_attr( $jacqueline_post_format )
				. ' post_layout_classic post_layout_classic_' . esc_attr( $jacqueline_columns )
				. ' post_layout_' . esc_attr( $jacqueline_blog_style[0] )
				. ' post_layout_' . esc_attr( $jacqueline_blog_style[0] ) . '_' . esc_attr( $jacqueline_columns )
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

	// Featured image
	$jacqueline_hover      = ! empty( $jacqueline_template_args['hover'] ) && ! jacqueline_is_inherit( $jacqueline_template_args['hover'] )
							? $jacqueline_template_args['hover']
							: jacqueline_get_theme_option( 'image_hover' );

	$jacqueline_components = ! empty( $jacqueline_template_args['meta_parts'] )
							? ( is_array( $jacqueline_template_args['meta_parts'] )
								? $jacqueline_template_args['meta_parts']
								: explode( ',', $jacqueline_template_args['meta_parts'] )
								)
							: jacqueline_array_get_keys_by_value( jacqueline_get_theme_option( 'meta_parts' ) );

	jacqueline_show_post_featured( apply_filters( 'jacqueline_filter_args_featured',
		array(
			'thumb_size' => ! empty( $jacqueline_template_args['thumb_size'] )
				? $jacqueline_template_args['thumb_size']
				: jacqueline_get_thumb_size(
					'classic' == $jacqueline_blog_style[0]
						? ( strpos( jacqueline_get_theme_option( 'body_style' ), 'full' ) !== false
								? ( $jacqueline_columns > 2 ? 'big' : 'huge' )
								: ( $jacqueline_columns > 2
									? ( $jacqueline_expanded ? 'square' : 'square' )
									: ($jacqueline_columns > 1 ? 'square' : ( $jacqueline_expanded ? 'huge' : 'big' ))
									)
							)
						: ( strpos( jacqueline_get_theme_option( 'body_style' ), 'full' ) !== false
								? ( $jacqueline_columns > 2 ? 'masonry-big' : 'full' )
								: ($jacqueline_columns === 1 ? ( $jacqueline_expanded ? 'huge' : 'big' ) : ( $jacqueline_columns <= 2 && $jacqueline_expanded ? 'masonry-big' : 'masonry' ))
							)
			),
			'hover'      => $jacqueline_hover,
			'meta_parts' => $jacqueline_components,
			'no_links'   => ! empty( $jacqueline_template_args['no_links'] ),
        ),
        'content-classic',
        $jacqueline_template_args
    ) );

	// Title and post meta
	$jacqueline_show_title = get_the_title() != '';
	$jacqueline_show_meta  = count( $jacqueline_components ) > 0 && ! in_array( $jacqueline_hover, array( 'border', 'pull', 'slide', 'fade', 'info' ) );

	if ( $jacqueline_show_title ) {
		?>
		<div class="post_header entry-header">
			<?php

			// Post meta
			if ( apply_filters( 'jacqueline_filter_show_blog_meta', $jacqueline_show_meta, $jacqueline_components, 'classic' ) ) {
				if ( count( $jacqueline_components ) > 0 ) {
					do_action( 'jacqueline_action_before_post_meta' );
					jacqueline_show_post_meta(
						apply_filters(
							'jacqueline_filter_post_meta_args', array(
							'components' => join( ',', $jacqueline_components ),
							'seo'        => false,
							'echo'       => true,
						), $jacqueline_blog_style[0], $jacqueline_columns
						)
					);
					do_action( 'jacqueline_action_after_post_meta' );
				}
			}

			// Post title
			if ( apply_filters( 'jacqueline_filter_show_blog_title', true, 'classic' ) ) {
				do_action( 'jacqueline_action_before_post_title' );
				if ( empty( $jacqueline_template_args['no_links'] ) ) {
					the_title( sprintf( '<h4 class="post_title entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h4>' );
				} else {
					the_title( '<h4 class="post_title entry-title">', '</h4>' );
				}
				do_action( 'jacqueline_action_after_post_title' );
			}

			if( !in_array( $jacqueline_post_format, array( 'quote', 'aside', 'link', 'status' ) ) ) {
				// More button
				if ( apply_filters( 'jacqueline_filter_show_blog_readmore', ! $jacqueline_show_title || ! empty( $jacqueline_template_args['more_button'] ), 'classic' ) ) {
					if ( empty( $jacqueline_template_args['no_links'] ) ) {
						do_action( 'jacqueline_action_before_post_readmore' );
						jacqueline_show_post_more_link( $jacqueline_template_args, '<div class="more-wrap">', '</div>' );
						do_action( 'jacqueline_action_after_post_readmore' );
					}
				}
			}
			?>
		</div><!-- .entry-header -->
		<?php
	}

	// Post content
	if( in_array( $jacqueline_post_format, array( 'quote', 'aside', 'link', 'status' ) ) ) {
		ob_start();
		if (apply_filters('jacqueline_filter_show_blog_excerpt', empty($jacqueline_template_args['hide_excerpt']) && jacqueline_get_theme_option('excerpt_length') > 0, 'classic')) {
			jacqueline_show_post_content($jacqueline_template_args, '<div class="post_content_inner">', '</div>');
		}
		// More button
		if(! empty( $jacqueline_template_args['more_button'] )) {
			if ( empty( $jacqueline_template_args['no_links'] ) ) {
				do_action( 'jacqueline_action_before_post_readmore' );
				jacqueline_show_post_more_link( $jacqueline_template_args, '<div class="more-wrap">', '</div>' );
				do_action( 'jacqueline_action_after_post_readmore' );
			}
		}
		$jacqueline_content = ob_get_contents();
		ob_end_clean();
		jacqueline_show_layout($jacqueline_content, '<div class="post_content entry-content">', '</div><!-- .entry-content -->');
	}
	?>

</article></div><?php
// Need opening PHP-tag above, because <div> is a inline-block element (used as column)!
