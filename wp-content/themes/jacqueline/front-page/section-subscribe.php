<div class="front_page_section front_page_section_subscribe<?php
	$jacqueline_scheme = jacqueline_get_theme_option( 'front_page_subscribe_scheme' );
	if ( ! empty( $jacqueline_scheme ) && ! jacqueline_is_inherit( $jacqueline_scheme ) ) {
		echo ' scheme_' . esc_attr( $jacqueline_scheme );
	}
	echo ' front_page_section_paddings_' . esc_attr( jacqueline_get_theme_option( 'front_page_subscribe_paddings' ) );
	if ( jacqueline_get_theme_option( 'front_page_subscribe_stack' ) ) {
		echo ' sc_stack_section_on';
	}
?>"
		<?php
		$jacqueline_css      = '';
		$jacqueline_bg_image = jacqueline_get_theme_option( 'front_page_subscribe_bg_image' );
		if ( ! empty( $jacqueline_bg_image ) ) {
			$jacqueline_css .= 'background-image: url(' . esc_url( jacqueline_get_attachment_url( $jacqueline_bg_image ) ) . ');';
		}
		if ( ! empty( $jacqueline_css ) ) {
			echo ' style="' . esc_attr( $jacqueline_css ) . '"';
		}
		?>
>
<?php
	// Add anchor
	$jacqueline_anchor_icon = jacqueline_get_theme_option( 'front_page_subscribe_anchor_icon' );
	$jacqueline_anchor_text = jacqueline_get_theme_option( 'front_page_subscribe_anchor_text' );
if ( ( ! empty( $jacqueline_anchor_icon ) || ! empty( $jacqueline_anchor_text ) ) && shortcode_exists( 'trx_sc_anchor' ) ) {
	echo do_shortcode(
		'[trx_sc_anchor id="front_page_section_subscribe"'
									. ( ! empty( $jacqueline_anchor_icon ) ? ' icon="' . esc_attr( $jacqueline_anchor_icon ) . '"' : '' )
									. ( ! empty( $jacqueline_anchor_text ) ? ' title="' . esc_attr( $jacqueline_anchor_text ) . '"' : '' )
									. ']'
	);
}
?>
	<div class="front_page_section_inner front_page_section_subscribe_inner
	<?php
	if ( jacqueline_get_theme_option( 'front_page_subscribe_fullheight' ) ) {
		echo ' jacqueline-full-height sc_layouts_flex sc_layouts_columns_middle';
	}
	?>
			"
			<?php
			$jacqueline_css      = '';
			$jacqueline_bg_mask  = jacqueline_get_theme_option( 'front_page_subscribe_bg_mask' );
			$jacqueline_bg_color_type = jacqueline_get_theme_option( 'front_page_subscribe_bg_color_type' );
			if ( 'custom' == $jacqueline_bg_color_type ) {
				$jacqueline_bg_color = jacqueline_get_theme_option( 'front_page_subscribe_bg_color' );
			} elseif ( 'scheme_bg_color' == $jacqueline_bg_color_type ) {
				$jacqueline_bg_color = jacqueline_get_scheme_color( 'bg_color', $jacqueline_scheme );
			} else {
				$jacqueline_bg_color = '';
			}
			if ( ! empty( $jacqueline_bg_color ) && $jacqueline_bg_mask > 0 ) {
				$jacqueline_css .= 'background-color: ' . esc_attr(
					1 == $jacqueline_bg_mask ? $jacqueline_bg_color : jacqueline_hex2rgba( $jacqueline_bg_color, $jacqueline_bg_mask )
				) . ';';
			}
			if ( ! empty( $jacqueline_css ) ) {
				echo ' style="' . esc_attr( $jacqueline_css ) . '"';
			}
			?>
	>
		<div class="front_page_section_content_wrap front_page_section_subscribe_content_wrap content_wrap">
			<?php
			// Caption
			$jacqueline_caption = jacqueline_get_theme_option( 'front_page_subscribe_caption' );
			if ( ! empty( $jacqueline_caption ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
				?>
				<h2 class="front_page_section_caption front_page_section_subscribe_caption front_page_block_<?php echo ! empty( $jacqueline_caption ) ? 'filled' : 'empty'; ?>"><?php echo wp_kses( $jacqueline_caption, 'jacqueline_kses_content' ); ?></h2>
				<?php
			}

			// Description (text)
			$jacqueline_description = jacqueline_get_theme_option( 'front_page_subscribe_description' );
			if ( ! empty( $jacqueline_description ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
				?>
				<div class="front_page_section_description front_page_section_subscribe_description front_page_block_<?php echo ! empty( $jacqueline_description ) ? 'filled' : 'empty'; ?>"><?php echo wp_kses( wpautop( $jacqueline_description ), 'jacqueline_kses_content' ); ?></div>
				<?php
			}

			// Content
			$jacqueline_sc = jacqueline_get_theme_option( 'front_page_subscribe_shortcode' );
			if ( ! empty( $jacqueline_sc ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
				?>
				<div class="front_page_section_output front_page_section_subscribe_output front_page_block_<?php echo ! empty( $jacqueline_sc ) ? 'filled' : 'empty'; ?>">
				<?php
					jacqueline_show_layout( do_shortcode( $jacqueline_sc ) );
				?>
				</div>
				<?php
			}
			?>
		</div>
	</div>
</div>
