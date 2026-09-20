<?php
/**
 * The Portfolio template to display the content
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

$jacqueline_post_format = get_post_format();
$jacqueline_post_format = empty( $jacqueline_post_format ) ? 'standard' : str_replace( 'post-format-', '', $jacqueline_post_format );

?><div class="
<?php
if ( ! empty( $jacqueline_template_args['slider'] ) ) {
	echo ' slider-slide swiper-slide';
} else {
	echo ( jacqueline_is_blog_style_use_masonry( $jacqueline_blog_style[0] ) ? 'masonry_item masonry_item-1_' . esc_attr( $jacqueline_columns ) : esc_attr( $jacqueline_columns_class ));
}
?>
"><article id="post-<?php the_ID(); ?>" 
	<?php
	post_class(
		'post_item post_item_container post_format_' . esc_attr( $jacqueline_post_format )
		. ' post_layout_portfolio'
		. ' post_layout_portfolio_' . esc_attr( $jacqueline_columns )
		. ( 'portfolio' != $jacqueline_blog_style[0] ? ' ' . esc_attr( $jacqueline_blog_style[0] )  . '_' . esc_attr( $jacqueline_columns ) : '' )
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

	$jacqueline_hover   = ! empty( $jacqueline_template_args['hover'] ) && ! jacqueline_is_inherit( $jacqueline_template_args['hover'] )
								? $jacqueline_template_args['hover']
								: jacqueline_get_theme_option( 'image_hover' );

	if ( 'dots' == $jacqueline_hover ) {
		$jacqueline_post_link = empty( $jacqueline_template_args['no_links'] )
								? ( ! empty( $jacqueline_template_args['link'] )
									? $jacqueline_template_args['link']
									: get_permalink()
									)
								: '';
		$jacqueline_target    = ! empty( $jacqueline_post_link ) && jacqueline_is_external_url( $jacqueline_post_link )
								? ' target="_blank" rel="nofollow"'
								: '';
	}
	
	// Meta parts
	$jacqueline_components = ! empty( $jacqueline_template_args['meta_parts'] )
							? ( is_array( $jacqueline_template_args['meta_parts'] )
								? $jacqueline_template_args['meta_parts']
								: explode( ',', $jacqueline_template_args['meta_parts'] )
								)
							: jacqueline_array_get_keys_by_value( jacqueline_get_theme_option( 'meta_parts' ) );

	// Featured image
	jacqueline_show_post_featured( apply_filters( 'jacqueline_filter_args_featured',
        array(
			'hover'         => $jacqueline_hover,
			'no_links'      => ! empty( $jacqueline_template_args['no_links'] ),
			'thumb_size'    => ! empty( $jacqueline_template_args['thumb_size'] )
								? $jacqueline_template_args['thumb_size']
								: jacqueline_get_thumb_size(
									jacqueline_is_blog_style_use_masonry( $jacqueline_blog_style[0] )
										? (	strpos( jacqueline_get_theme_option( 'body_style' ), 'full' ) !== false || $jacqueline_columns < 3
											? 'masonry-big'
											: 'masonry'
											)
										: (	strpos( jacqueline_get_theme_option( 'body_style' ), 'full' ) !== false || $jacqueline_columns < 3
											? 'square'
											: 'square'
											)
								),
			'thumb_bg' => jacqueline_is_blog_style_use_masonry( $jacqueline_blog_style[0] ) ? false : true,
			'show_no_image' => true,
			'meta_parts'    => $jacqueline_components,
			'class'         => 'dots' == $jacqueline_hover ? 'hover_with_info' : '',
			'post_info'     => 'dots' == $jacqueline_hover
										? '<div class="post_info"><h5 class="post_title">'
											. ( ! empty( $jacqueline_post_link )
												? '<a href="' . esc_url( $jacqueline_post_link ) . '"' . ( ! empty( $target ) ? $target : '' ) . '>'
												: ''
												)
												. esc_html( get_the_title() ) 
											. ( ! empty( $jacqueline_post_link )
												? '</a>'
												: ''
												)
											. '</h5></div>'
										: '',
            'thumb_ratio'   => 'info' == $jacqueline_hover ?  '100:102' : '',
        ),
        'content-portfolio',
        $jacqueline_template_args
    ) );
	?>
</article></div><?php
// Need opening PHP-tag above, because <article> is a inline-block element (used as column)!