<?php
/**
 * The Footer: widgets area, logo, footer menu and socials
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0
 */

							do_action( 'jacqueline_action_page_content_end_text' );
							
							// Widgets area below the content
							jacqueline_create_widgets_area( 'widgets_below_content' );
						
							do_action( 'jacqueline_action_page_content_end' );
							?>
						</div>
						<?php
						
						do_action( 'jacqueline_action_after_page_content' );

						// Show main sidebar
						get_sidebar();

						do_action( 'jacqueline_action_content_wrap_end' );
						?>
					</div>
					<?php

					do_action( 'jacqueline_action_after_content_wrap' );

					// Widgets area below the page and related posts below the page
					$jacqueline_body_style = jacqueline_get_theme_option( 'body_style' );
					$jacqueline_widgets_name = jacqueline_get_theme_option( 'widgets_below_page', 'hide' );
					$jacqueline_show_widgets = ! jacqueline_is_off( $jacqueline_widgets_name ) && is_active_sidebar( $jacqueline_widgets_name );
					$jacqueline_show_related = jacqueline_is_single() && jacqueline_get_theme_option( 'related_position', 'below_content' ) == 'below_page';
					if ( $jacqueline_show_widgets || $jacqueline_show_related ) {
						if ( 'fullscreen' != $jacqueline_body_style ) {
							?>
							<div class="content_wrap">
							<?php
						}
						// Show related posts before footer
						if ( $jacqueline_show_related ) {
							do_action( 'jacqueline_action_related_posts' );
						}

						// Widgets area below page content
						if ( $jacqueline_show_widgets ) {
							jacqueline_create_widgets_area( 'widgets_below_page' );
						}
						if ( 'fullscreen' != $jacqueline_body_style ) {
							?>
							</div>
							<?php
						}
					}
					do_action( 'jacqueline_action_page_content_wrap_end' );
					?>
			</div>
			<?php
			do_action( 'jacqueline_action_after_page_content_wrap' );

			// Don't display the footer elements while actions 'full_post_loading' and 'prev_post_loading'
			if ( ( ! jacqueline_is_singular( 'post' ) && ! jacqueline_is_singular( 'attachment' ) ) || ! in_array ( jacqueline_get_value_gp( 'action' ), array( 'full_post_loading', 'prev_post_loading' ) ) ) {
				
				// Skip link anchor to fast access to the footer from keyboard
				?>
				<span id="footer_skip_link_anchor" class="jacqueline_skip_link_anchor"></span>
				<?php

				do_action( 'jacqueline_action_before_footer' );

				// Footer
				$jacqueline_footer_type = jacqueline_get_theme_option( 'footer_type' );
				if ( 'custom' == $jacqueline_footer_type && ! jacqueline_is_layouts_available() ) {
					$jacqueline_footer_type = 'default';
				}
				get_template_part( apply_filters( 'jacqueline_filter_get_template_part', "templates/footer-" . sanitize_file_name( $jacqueline_footer_type ) ) );

				do_action( 'jacqueline_action_after_footer' );

			}
			?>

			<?php do_action( 'jacqueline_action_page_wrap_end' ); ?>

		</div>

		<?php do_action( 'jacqueline_action_after_page_wrap' ); ?>

	</div>

	<?php do_action( 'jacqueline_action_after_body' ); ?>

	<?php wp_footer(); ?>

</body>
</html>