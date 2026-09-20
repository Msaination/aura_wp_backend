<?php
/**
 * The default template to display the content
 *
 * Used for index/archive/search.
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0
 */

$jacqueline_template_args = get_query_var( 'jacqueline_template_args' );
$jacqueline_columns = 1;
if ( is_array( $jacqueline_template_args ) ) {
	$jacqueline_columns    = empty( $jacqueline_template_args['columns'] ) ? 1 : max( 1, $jacqueline_template_args['columns'] );
	$jacqueline_blog_style = array( $jacqueline_template_args['type'], $jacqueline_columns );
	if ( ! empty( $jacqueline_template_args['slider'] ) ) {
		?><div class="slider-slide swiper-slide">
		<?php
	} elseif ( $jacqueline_columns > 1 ) {
	    $jacqueline_columns_class = jacqueline_get_column_class( 1, $jacqueline_columns, ! empty( $jacqueline_template_args['columns_tablet']) ? $jacqueline_template_args['columns_tablet'] : '', ! empty($jacqueline_template_args['columns_mobile']) ? $jacqueline_template_args['columns_mobile'] : '' );
		?>
		<div class="<?php echo esc_attr( $jacqueline_columns_class ); ?>">
		<?php
	}
} else {
	$jacqueline_template_args = array();
}
$jacqueline_expanded    = ! jacqueline_sidebar_present() && jacqueline_get_theme_option( 'expand_content' ) == 'expand';
$jacqueline_post_format = get_post_format();
$jacqueline_post_format = empty( $jacqueline_post_format ) ? 'standard' : str_replace( 'post-format-', '', $jacqueline_post_format );
?>
<article id="post-<?php the_ID(); ?>" data-post-id="<?php the_ID(); ?>"
	<?php
	post_class( 'post_item post_item_container post_layout_excerpt post_format_' . esc_attr( $jacqueline_post_format ) );
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
			'thumb_size' => ! empty( $jacqueline_template_args['thumb_size'] )
							? $jacqueline_template_args['thumb_size']
							: jacqueline_get_thumb_size( strpos( jacqueline_get_theme_option( 'body_style' ), 'full' ) !== false
								? 'full'
								: ( $jacqueline_expanded 
									? 'huge' 
									: 'big' 
									)
								),
		),
		'content-excerpt',
		$jacqueline_template_args
	) );

	// Title and post meta
	$jacqueline_show_title = get_the_title() != '';
	$jacqueline_show_meta  = count( $jacqueline_components ) > 0 && ! in_array( $jacqueline_hover, array( 'border', 'pull', 'slide', 'fade', 'info' ) );

	if ( $jacqueline_show_title ) {
		?>
		<div class="post_header entry-header">
			<?php
			// Post title
			if ( apply_filters( 'jacqueline_filter_show_blog_title', true, 'excerpt' ) ) {
				do_action( 'jacqueline_action_before_post_title' );
				if ( empty( $jacqueline_template_args['no_links'] ) ) {
					the_title( sprintf( '<h3 class="post_title entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h3>' );
				} else {
					the_title( '<h3 class="post_title entry-title">', '</h3>' );
				}
				do_action( 'jacqueline_action_after_post_title' );
			}
			?>
		</div><!-- .post_header -->
		<?php
	}

	// Post content
	if ( apply_filters( 'jacqueline_filter_show_blog_excerpt', empty( $jacqueline_template_args['hide_excerpt'] ) && jacqueline_get_theme_option( 'excerpt_length' ) > 0, 'excerpt' ) ) {
		?>
		<div class="post_content entry-content">
			<?php

			// Post meta
			if ( apply_filters( 'jacqueline_filter_show_blog_meta', $jacqueline_show_meta, $jacqueline_components, 'excerpt' ) ) {
				if ( count( $jacqueline_components ) > 0 ) {
					do_action( 'jacqueline_action_before_post_meta' );
					jacqueline_show_post_meta(
						apply_filters(
							'jacqueline_filter_post_meta_args', array(
								'components' => join( ',', $jacqueline_components ),
								'seo'        => false,
								'echo'       => true,
							), 'excerpt', 1
						)
					);
					do_action( 'jacqueline_action_after_post_meta' );
				}
			}

			if ( jacqueline_get_theme_option( 'blog_content' ) == 'fullpost' ) {
				// Post content area
				?>
				<div class="post_content_inner">
					<?php
					do_action( 'jacqueline_action_before_full_post_content' );
					the_content( '' );
					do_action( 'jacqueline_action_after_full_post_content' );
					?>
				</div>
				<?php
				// Inner pages
				wp_link_pages(
					array(
						'before'      => '<div class="page_links"><span class="page_links_title">' . esc_html__( 'Pages:', 'jacqueline' ) . '</span>',
						'after'       => '</div>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
						'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'jacqueline' ) . ' </span>%',
						'separator'   => '<span class="screen-reader-text">, </span>',
					)
				);
			} else {
				// Post content area
				jacqueline_show_post_content( $jacqueline_template_args, '<div class="post_content_inner">', '</div>' );
			}

			// More button
			if ( apply_filters( 'jacqueline_filter_show_blog_readmore',  ! isset( $jacqueline_template_args['more_button'] ) || ! empty( $jacqueline_template_args['more_button'] ), 'excerpt' ) ) {
				if ( empty( $jacqueline_template_args['no_links'] ) ) {
					do_action( 'jacqueline_action_before_post_readmore' );
					if ( jacqueline_get_theme_option( 'blog_content' ) != 'fullpost' ) {
						jacqueline_show_post_more_link( $jacqueline_template_args, '<p>', '</p>' );
					} else {
						jacqueline_show_post_comments_link( $jacqueline_template_args, '<p>', '</p>' );
					}
					do_action( 'jacqueline_action_after_post_readmore' );
				}
			}

			?>
		</div><!-- .entry-content -->
		<?php
	}
	?>
</article>
<?php

if ( is_array( $jacqueline_template_args ) ) {
	if ( ! empty( $jacqueline_template_args['slider'] ) || $jacqueline_columns > 1 ) {
		?>
		</div>
		<?php
	}
}
