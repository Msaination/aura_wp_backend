<?php
/**
 * The template to display single post
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0
 */

// Full post loading
$full_post_loading          = jacqueline_get_value_gp( 'action' ) == 'full_post_loading';

// Prev post loading
$prev_post_loading          = jacqueline_get_value_gp( 'action' ) == 'prev_post_loading';
$prev_post_loading_type     = jacqueline_get_theme_option( 'posts_navigation_scroll_which_block', 'article' );

// Position of the related posts
$jacqueline_related_position   = jacqueline_get_theme_option( 'related_position', 'below_content' );

// Type of the prev/next post navigation
$jacqueline_posts_navigation   = jacqueline_get_theme_option( 'posts_navigation' );
$jacqueline_prev_post          = false;
$jacqueline_prev_post_same_cat = (int)jacqueline_get_theme_option( 'posts_navigation_scroll_same_cat', 1 );

// Rewrite style of the single post if current post loading via AJAX and featured image and title is not in the content
if ( ( $full_post_loading 
		|| 
		( $prev_post_loading && 'article' == $prev_post_loading_type )
	) 
	&& 
	! in_array( jacqueline_get_theme_option( 'single_style' ), array( 'style-6' ) )
) {
	jacqueline_storage_set_array( 'options_meta', 'single_style', 'style-6' );
}

do_action( 'jacqueline_action_prev_post_loading', $prev_post_loading, $prev_post_loading_type );

get_header();

while ( have_posts() ) {

	the_post();

	// Type of the prev/next post navigation
	if ( 'scroll' == $jacqueline_posts_navigation ) {
		$jacqueline_prev_post = get_previous_post( $jacqueline_prev_post_same_cat );  // Get post from same category
		if ( ! $jacqueline_prev_post && $jacqueline_prev_post_same_cat ) {
			$jacqueline_prev_post = get_previous_post( false );                    // Get post from any category
		}
		if ( ! $jacqueline_prev_post ) {
			$jacqueline_posts_navigation = 'links';
		}
	}

	// Override some theme options to display featured image, title and post meta in the dynamic loaded posts
	if ( $full_post_loading || ( $prev_post_loading && $jacqueline_prev_post ) ) {
		jacqueline_sc_layouts_showed( 'featured', false );
		jacqueline_sc_layouts_showed( 'title', false );
		jacqueline_sc_layouts_showed( 'postmeta', false );
	}

	// If related posts should be inside the content
	if ( strpos( $jacqueline_related_position, 'inside' ) === 0 ) {
		ob_start();
	}

	// Display post's content
	get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/content', 'single-' . jacqueline_get_theme_option( 'single_style' ) ), 'single-' . jacqueline_get_theme_option( 'single_style' ) );

	// If related posts should be inside the content
	if ( strpos( $jacqueline_related_position, 'inside' ) === 0 ) {
		$jacqueline_content = ob_get_contents();
		ob_end_clean();

		ob_start();
		do_action( 'jacqueline_action_related_posts' );
		$jacqueline_related_content = ob_get_contents();
		ob_end_clean();

		if ( ! empty( $jacqueline_related_content ) ) {
			$jacqueline_related_position_inside = max( 0, min( 9, jacqueline_get_theme_option( 'related_position_inside' ) ) );
			if ( 0 == $jacqueline_related_position_inside ) {
				$jacqueline_related_position_inside = mt_rand( 1, 9 );
			}

			$jacqueline_p_number         = 0;
			$jacqueline_related_inserted = false;
			$jacqueline_in_block         = false;
			$jacqueline_content_start    = strpos( $jacqueline_content, '<div class="post_content' );
			$jacqueline_content_end      = strrpos( $jacqueline_content, '</div>' );

			for ( $i = max( 0, $jacqueline_content_start ); $i < min( strlen( $jacqueline_content ) - 3, $jacqueline_content_end ); $i++ ) {
				if ( $jacqueline_content[ $i ] != '<' ) {
					continue;
				}
				if ( $jacqueline_in_block ) {
					if ( strtolower( substr( $jacqueline_content, $i + 1, 12 ) ) == '/blockquote>' ) {
						$jacqueline_in_block = false;
						$i += 12;
					}
					continue;
				} else if ( strtolower( substr( $jacqueline_content, $i + 1, 10 ) ) == 'blockquote' && in_array( $jacqueline_content[ $i + 11 ], array( '>', ' ' ) ) ) {
					$jacqueline_in_block = true;
					$i += 11;
					continue;
				} else if ( 'p' == $jacqueline_content[ $i + 1 ] && in_array( $jacqueline_content[ $i + 2 ], array( '>', ' ' ) ) ) {
					$jacqueline_p_number++;
					if ( $jacqueline_related_position_inside == $jacqueline_p_number ) {
						$jacqueline_related_inserted = true;
						$jacqueline_content = ( $i > 0 ? substr( $jacqueline_content, 0, $i ) : '' )
											. $jacqueline_related_content
											. substr( $jacqueline_content, $i );
					}
				}
			}
			if ( ! $jacqueline_related_inserted ) {
				if ( $jacqueline_content_end > 0 ) {
					$jacqueline_content = substr( $jacqueline_content, 0, $jacqueline_content_end ) . $jacqueline_related_content . substr( $jacqueline_content, $jacqueline_content_end );
				} else {
					$jacqueline_content .= $jacqueline_related_content;
				}
			}
		}

		jacqueline_show_layout( $jacqueline_content );
	}

	// Comments
	do_action( 'jacqueline_action_before_comments' );
	comments_template();
	do_action( 'jacqueline_action_after_comments' );

	// Related posts
	if ( 'below_content' == $jacqueline_related_position
		&& ( 'scroll' != $jacqueline_posts_navigation || (int)jacqueline_get_theme_option( 'posts_navigation_scroll_hide_related', 0 ) == 0 )
		&& ( ! $full_post_loading || (int)jacqueline_get_theme_option( 'open_full_post_hide_related', 1 ) == 0 )
	) {
		do_action( 'jacqueline_action_related_posts' );
	}

	// Post navigation: type 'scroll'
	if ( 'scroll' == $jacqueline_posts_navigation && ! $full_post_loading ) {
		?>
		<div class="nav-links-single-scroll"
			data-post-id="<?php echo esc_attr( get_the_ID( $jacqueline_prev_post ) ); ?>"
			data-post-link="<?php echo esc_attr( get_permalink( $jacqueline_prev_post ) ); ?>"
			data-post-title="<?php the_title_attribute( array( 'post' => $jacqueline_prev_post ) ); ?>"
			data-cur-post-link="<?php echo esc_attr( get_permalink() ); ?>"
			data-cur-post-title="<?php the_title_attribute(); ?>"
			<?php do_action( 'jacqueline_action_nav_links_single_scroll_data', $jacqueline_prev_post ); ?>
		></div>
		<?php
	}
}

get_footer();
