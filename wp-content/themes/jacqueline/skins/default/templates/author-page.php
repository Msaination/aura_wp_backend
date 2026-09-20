<?php
/**
 * The template to display the user's avatar, bio and socials on the Author page
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.71.0
 */
?>

<div class="author_page author vcard"<?php
	if ( jacqueline_is_on( jacqueline_get_theme_option( 'seo_snippets' ) ) ) {
		?> itemprop="author" itemscope="itemscope" itemtype="<?php echo esc_attr( jacqueline_get_protocol( true ) ); ?>//schema.org/Person"<?php
	}
?>>

	<div class="author_avatar"<?php
		if ( jacqueline_is_on( jacqueline_get_theme_option( 'seo_snippets' ) ) ) {
			?> itemprop="image"<?php
		}
	?>>
		<?php
		$jacqueline_mult = jacqueline_get_retina_multiplier();
		echo get_avatar( get_the_author_meta( 'user_email' ), 120 * $jacqueline_mult );
		?>
	</div>

	<h4 class="author_title"<?php
		if ( jacqueline_is_on( jacqueline_get_theme_option( 'seo_snippets' ) ) ) {
			?> itemprop="name"<?php
		}
	?>><span class="fn"><?php the_author(); ?></span></h4>

	<?php
	$jacqueline_author_description = get_the_author_meta( 'description' );
	if ( ! empty( $jacqueline_author_description ) ) {
		?>
		<div class="author_bio"<?php
			if ( jacqueline_is_on( jacqueline_get_theme_option( 'seo_snippets' ) ) ) {
				?> itemprop="description"<?php
			}
		?>><?php echo wp_kses( wpautop( $jacqueline_author_description ), 'jacqueline_kses_content' ); ?></div>
		<?php
	}
	?>

	<div class="author_details">
		<span class="author_posts_total">
			<?php
			$jacqueline_posts_total = count_user_posts( get_the_author_meta('ID'), 'post' );
			if ( $jacqueline_posts_total > 0 ) {
				// Translators: Add the author's posts number to the message
				echo wp_kses( sprintf( _n( '%s article published', '%s articles published', $jacqueline_posts_total, 'jacqueline' ),
										'<span class="author_posts_total_value">' . number_format_i18n( $jacqueline_posts_total ) . '</span>'
								 		),
							'jacqueline_kses_content'
							);
			} else {
				esc_html_e( 'No posts published.', 'jacqueline' );
			}
			?>
		</span><?php
			ob_start();
			do_action( 'jacqueline_action_user_meta', 'author-page' );
			$jacqueline_socials = ob_get_contents();
			ob_end_clean();
			jacqueline_show_layout( $jacqueline_socials,
				'<span class="author_socials"><span class="author_socials_caption">' . esc_html__( 'Follow:', 'jacqueline' ) . '</span>',
				'</span>'
			);
		?>
	</div>

</div>
