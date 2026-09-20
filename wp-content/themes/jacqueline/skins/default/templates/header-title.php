<?php
/**
 * The template to display the page title and breadcrumbs
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0
 */

// Page (category, tag, archive, author) title

if ( jacqueline_need_page_title() ) {
	jacqueline_sc_layouts_showed( 'title', true );
	jacqueline_sc_layouts_showed( 'postmeta', true );
	?>
	<div class="top_panel_title sc_layouts_row sc_layouts_row_type_normal">
		<div class="content_wrap">
			<div class="sc_layouts_column sc_layouts_column_align_center">
				<div class="sc_layouts_item">
					<div class="sc_layouts_title sc_align_center">
						<?php
						// Post meta on the single post
						if ( is_single() ) {
							?>
							<div class="sc_layouts_title_meta">
							<?php
								jacqueline_show_post_meta(
									apply_filters(
										'jacqueline_filter_post_meta_args', array(
											'components' => join( ',', jacqueline_array_get_keys_by_value( jacqueline_get_theme_option( 'meta_parts' ) ) ),
											'counters'   => join( ',', jacqueline_array_get_keys_by_value( jacqueline_get_theme_option( 'counters' ) ) ),
											'seo'        => jacqueline_is_on( jacqueline_get_theme_option( 'seo_snippets' ) ),
										), 'header', 1
									)
								);
							?>
							</div>
							<?php
						}

						// Blog/Post title
						?>
						<div class="sc_layouts_title_title">
							<?php
							$jacqueline_blog_title           = jacqueline_get_blog_title();
							$jacqueline_blog_title_text      = '';
							$jacqueline_blog_title_class     = '';
							$jacqueline_blog_title_link      = '';
							$jacqueline_blog_title_link_text = '';
							if ( is_array( $jacqueline_blog_title ) ) {
								$jacqueline_blog_title_text      = $jacqueline_blog_title['text'];
								$jacqueline_blog_title_class     = ! empty( $jacqueline_blog_title['class'] ) ? ' ' . $jacqueline_blog_title['class'] : '';
								$jacqueline_blog_title_link      = ! empty( $jacqueline_blog_title['link'] ) ? $jacqueline_blog_title['link'] : '';
								$jacqueline_blog_title_link_text = ! empty( $jacqueline_blog_title['link_text'] ) ? $jacqueline_blog_title['link_text'] : '';
							} else {
								$jacqueline_blog_title_text = $jacqueline_blog_title;
							}
							?>
							<h1 class="sc_layouts_title_caption<?php echo esc_attr( $jacqueline_blog_title_class ); ?>"<?php
								if ( jacqueline_is_on( jacqueline_get_theme_option( 'seo_snippets' ) ) ) {
									?> itemprop="headline"<?php
								}
							?>>
								<?php
								$jacqueline_top_icon = jacqueline_get_term_image_small();
								if ( ! empty( $jacqueline_top_icon ) ) {
									$jacqueline_attr = jacqueline_getimagesize( $jacqueline_top_icon );
									?>
									<img src="<?php echo esc_url( $jacqueline_top_icon ); ?>" alt="<?php esc_attr_e( 'Site icon', 'jacqueline' ); ?>"
										<?php
										if ( ! empty( $jacqueline_attr[3] ) ) {
											jacqueline_show_layout( $jacqueline_attr[3] );
										}
										?>
									>
									<?php
								}
								echo wp_kses_data( $jacqueline_blog_title_text );
								?>
							</h1>
							<?php
							if ( ! empty( $jacqueline_blog_title_link ) && ! empty( $jacqueline_blog_title_link_text ) ) {
								?>
								<a href="<?php echo esc_url( $jacqueline_blog_title_link ); ?>" class="theme_button theme_button_small sc_layouts_title_link"><?php echo esc_html( $jacqueline_blog_title_link_text ); ?></a>
								<?php
							}

							// Category/Tag description
							if ( ! is_paged() && ( is_category() || is_tag() || is_tax() ) ) {
								the_archive_description( '<div class="sc_layouts_title_description">', '</div>' );
							}

							?>
						</div>
						<?php

						// Breadcrumbs
						ob_start();
						do_action( 'jacqueline_action_breadcrumbs' );
						$jacqueline_breadcrumbs = ob_get_contents();
						ob_end_clean();
						jacqueline_show_layout( $jacqueline_breadcrumbs, '<div class="sc_layouts_title_breadcrumbs">', '</div>' );
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
