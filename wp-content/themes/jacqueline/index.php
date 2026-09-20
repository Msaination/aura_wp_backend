<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: //codex.wordpress.org/Template_Hierarchy
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0
 */

$jacqueline_template = apply_filters( 'jacqueline_filter_get_template_part', jacqueline_blog_archive_get_template() );

if ( ! empty( $jacqueline_template ) && 'index' != $jacqueline_template ) {

	get_template_part( $jacqueline_template );

} else {

	jacqueline_storage_set( 'blog_archive', true );

	get_header();

	if ( have_posts() ) {

		// Query params
		$jacqueline_stickies   = is_home()
								|| ( in_array( jacqueline_get_theme_option( 'post_type' ), array( '', 'post' ) )
									&& (int) jacqueline_get_theme_option( 'parent_cat' ) == 0
									)
										? get_option( 'sticky_posts' )
										: false;
		$jacqueline_post_type  = jacqueline_get_theme_option( 'post_type' );
		$jacqueline_args       = array(
								'blog_style'     => jacqueline_get_theme_option( 'blog_style' ),
								'post_type'      => $jacqueline_post_type,
								'taxonomy'       => jacqueline_get_post_type_taxonomy( $jacqueline_post_type ),
								'parent_cat'     => jacqueline_get_theme_option( 'parent_cat' ),
								'posts_per_page' => jacqueline_get_theme_option( 'posts_per_page' ),
								'sticky'         => jacqueline_get_theme_option( 'sticky_style', 'inherit' ) == 'columns'
															&& is_array( $jacqueline_stickies )
															&& count( $jacqueline_stickies ) > 0
															&& get_query_var( 'paged' ) < 1
								);

		jacqueline_blog_archive_start();

		do_action( 'jacqueline_action_blog_archive_start' );

		if ( is_author() ) {
			do_action( 'jacqueline_action_before_page_author' );
			get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/author-page' ) );
			do_action( 'jacqueline_action_after_page_author' );
		}

		if ( jacqueline_get_theme_option( 'show_filters', 0 ) ) {
			do_action( 'jacqueline_action_before_page_filters' );
			jacqueline_show_filters( $jacqueline_args );
			do_action( 'jacqueline_action_after_page_filters' );
		} else {
			do_action( 'jacqueline_action_before_page_posts' );
			jacqueline_show_posts( array_merge( $jacqueline_args, array( 'cat' => $jacqueline_args['parent_cat'] ) ) );
			do_action( 'jacqueline_action_after_page_posts' );
		}

		do_action( 'jacqueline_action_blog_archive_end' );

		jacqueline_blog_archive_end();

	} else {

		if ( is_search() ) {
			get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/content', 'none-search' ), 'none-search' );
		} else {
			get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/content', 'none-archive' ), 'none-archive' );
		}
	}

	get_footer();
}
