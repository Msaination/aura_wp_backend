<?php
/**
 * The Sticky template to display the sticky posts
 *
 * Used for index/archive
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0
 */

$jacqueline_columns     = max( 1, min( 3, count( get_option( 'sticky_posts' ) ) ) );
$jacqueline_post_format = get_post_format();
$jacqueline_post_format = empty( $jacqueline_post_format ) ? 'standard' : str_replace( 'post-format-', '', $jacqueline_post_format );

?><div class="column-1_<?php echo esc_attr( $jacqueline_columns ); ?>"><article id="post-<?php the_ID(); ?>" 
	<?php
	post_class( 'post_item post_layout_sticky post_format_' . esc_attr( $jacqueline_post_format ) );
	jacqueline_add_blog_animation( $jacqueline_template_args );
	?>
>

	<?php
	if ( is_sticky() && is_home() && ! is_paged() ) {
		?>
		<span class="post_label label_sticky"></span>
		<?php
	}

	// Featured image
	jacqueline_show_post_featured(
		array(
			'thumb_size' => jacqueline_get_thumb_size( 1 == $jacqueline_columns ? 'big' : ( 2 == $jacqueline_columns ? 'med' : 'avatar' ) ),
		)
	);

	if ( ! in_array( $jacqueline_post_format, array( 'link', 'aside', 'status', 'quote' ) ) ) {
		?>
		<div class="post_header entry-header">
			<?php
			// Post title
			the_title( sprintf( '<h5 class="post_title entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h6>' );
			// Post meta
			jacqueline_show_post_meta( apply_filters( 'jacqueline_filter_post_meta_args', array(), 'sticky', $jacqueline_columns ) );
			?>
		</div><!-- .entry-header -->
		<?php
	}
	?>
</article></div><?php

// div.column-1_X is a inline-block and new lines and spaces after it are forbidden
