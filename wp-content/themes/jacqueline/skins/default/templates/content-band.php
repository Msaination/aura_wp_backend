<?php
/**
 * 'Band' template to display the content
 *
 * Used for index/archive/search.
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.71.0
 */

$jacqueline_template_args = get_query_var( 'jacqueline_template_args' );
if ( ! is_array( $jacqueline_template_args ) ) {
	$jacqueline_template_args = array(
								'type'    => 'band',
								'columns' => 1
								);
}

$jacqueline_columns       = 1;

$jacqueline_expanded      = ! jacqueline_sidebar_present() && jacqueline_get_theme_option( 'expand_content' ) == 'expand';

$jacqueline_post_format   = get_post_format();
$jacqueline_post_format   = empty( $jacqueline_post_format ) ? 'standard' : str_replace( 'post-format-', '', $jacqueline_post_format );

if ( is_array( $jacqueline_template_args ) ) {
	$jacqueline_columns    = empty( $jacqueline_template_args['columns'] ) ? 1 : max( 1, $jacqueline_template_args['columns'] );
	$jacqueline_blog_style = array( $jacqueline_template_args['type'], $jacqueline_columns );
	if ( ! empty( $jacqueline_template_args['slider'] ) ) {
		?><div class="slider-slide swiper-slide">
		<?php
	} elseif ( $jacqueline_columns > 1 ) {
	    $jacqueline_columns_class = jacqueline_get_column_class( 1, $jacqueline_columns, ! empty( $jacqueline_template_args['columns_tablet']) ? $jacqueline_template_args['columns_tablet'] : '', ! empty($jacqueline_template_args['columns_mobile']) ? $jacqueline_template_args['columns_mobile'] : '' );
				?><div class="<?php echo esc_attr( $jacqueline_columns_class ); ?>"><?php
	}
}
?>
<article id="post-<?php the_ID(); ?>" data-post-id="<?php the_ID(); ?>"
	<?php
	post_class( 'post_item post_item_container post_layout_band post_format_' . esc_attr( $jacqueline_post_format ) );
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
								: array_map( 'trim', explode( ',', $jacqueline_template_args['meta_parts'] ) )
								)
							: jacqueline_array_get_keys_by_value( jacqueline_get_theme_option( 'meta_parts' ) );
	jacqueline_show_post_featured( apply_filters( 'jacqueline_filter_args_featured',
		array(
			'no_links'   => ! empty( $jacqueline_template_args['no_links'] ),
			'hover'      => $jacqueline_hover,
			'meta_parts' => $jacqueline_components,
			'thumb_bg'   => true,
			'thumb_ratio'   => '1:1',
			'thumb_size' => ! empty( $jacqueline_template_args['thumb_size'] )
								? $jacqueline_template_args['thumb_size']
								: jacqueline_get_thumb_size( 
								in_array( $jacqueline_post_format, array( 'gallery', 'audio', 'video' ) )
									? ( strpos( jacqueline_get_theme_option( 'body_style' ), 'full' ) !== false
										? 'full'
										: ( $jacqueline_expanded 
											? 'big' 
											: 'medium-square'
											)
										)
									: 'masonry-big'
								)
		),
		'content-band',
		$jacqueline_template_args
	) );

	?><div class="post_content_wrap"><?php

		// Title and post meta
		$jacqueline_show_title = get_the_title() != '';
		$jacqueline_show_meta  = count( $jacqueline_components ) > 0 && ! in_array( $jacqueline_hover, array( 'border', 'pull', 'slide', 'fade', 'info' ) );
		if ( $jacqueline_show_title ) {
			?>
			<div class="post_header entry-header">
				<?php
				// Categories
				if ( apply_filters( 'jacqueline_filter_show_blog_categories', $jacqueline_show_meta && in_array( 'categories', $jacqueline_components ), array( 'categories' ), 'band' ) ) {
					do_action( 'jacqueline_action_before_post_category' );
					?>
					<div class="post_category">
						<?php
						jacqueline_show_post_meta( apply_filters(
															'jacqueline_filter_post_meta_args',
															array(
																'components' => 'categories',
																'seo'        => false,
																'echo'       => true,
																'cat_sep'    => false,
																),
															'hover_' . $jacqueline_hover, 1
															)
											);
						?>
					</div>
					<?php
					$jacqueline_components = jacqueline_array_delete_by_value( $jacqueline_components, 'categories' );
					do_action( 'jacqueline_action_after_post_category' );
				}
				// Post title
				if ( apply_filters( 'jacqueline_filter_show_blog_title', true, 'band' ) ) {
					do_action( 'jacqueline_action_before_post_title' );
					if ( empty( $jacqueline_template_args['no_links'] ) ) {
						the_title( sprintf( '<h4 class="post_title entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h4>' );
					} else {
						the_title( '<h4 class="post_title entry-title">', '</h4>' );
					}
					do_action( 'jacqueline_action_after_post_title' );
				}
				?>
			</div><!-- .post_header -->
			<?php
		}

		// Post content
		if ( ! isset( $jacqueline_template_args['excerpt_length'] ) && ! in_array( $jacqueline_post_format, array( 'gallery', 'audio', 'video' ) ) ) {
			$jacqueline_template_args['excerpt_length'] = 13;
		}
		if ( apply_filters( 'jacqueline_filter_show_blog_excerpt', empty( $jacqueline_template_args['hide_excerpt'] ) && jacqueline_get_theme_option( 'excerpt_length' ) > 0, 'band' ) ) {
			?>
			<div class="post_content entry-content">
				<?php
				// Post content area
				jacqueline_show_post_content( $jacqueline_template_args, '<div class="post_content_inner">', '</div>' );
				?>
			</div><!-- .entry-content -->
			<?php
		}
		// Post meta
		if ( apply_filters( 'jacqueline_filter_show_blog_meta', $jacqueline_show_meta, $jacqueline_components, 'band' ) ) {
			if ( count( $jacqueline_components ) > 0 ) {
				do_action( 'jacqueline_action_before_post_meta' );
				jacqueline_show_post_meta(
					apply_filters(
						'jacqueline_filter_post_meta_args', array(
							'components' => join( ',', $jacqueline_components ),
							'seo'        => false,
							'echo'       => true,
						), 'band', 1
					)
				);
				do_action( 'jacqueline_action_after_post_meta' );
			}
		}
		// More button
		if ( apply_filters( 'jacqueline_filter_show_blog_readmore', ! $jacqueline_show_title || ! empty( $jacqueline_template_args['more_button'] ), 'band' ) ) {
			if ( empty( $jacqueline_template_args['no_links'] ) ) {
				do_action( 'jacqueline_action_before_post_readmore' );
				jacqueline_show_post_more_link( $jacqueline_template_args, '<div class="more-wrap">', '</div>' );
				do_action( 'jacqueline_action_after_post_readmore' );
			}
		}
		?>
	</div>
</article>
<?php

if ( is_array( $jacqueline_template_args ) ) {
	if ( ! empty( $jacqueline_template_args['slider'] ) || $jacqueline_columns > 1 ) {
		?>
		</div>
		<?php
	}
}