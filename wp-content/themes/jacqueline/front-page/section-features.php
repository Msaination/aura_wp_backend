<div class="front_page_section front_page_section_features<?php
	$jacqueline_scheme = jacqueline_get_theme_option( 'front_page_features_scheme' );
	if ( ! empty( $jacqueline_scheme ) && ! jacqueline_is_inherit( $jacqueline_scheme ) ) {
		echo ' scheme_' . esc_attr( $jacqueline_scheme );
	}
	echo ' front_page_section_paddings_' . esc_attr( jacqueline_get_theme_option( 'front_page_features_paddings' ) );
	if ( jacqueline_get_theme_option( 'front_page_features_stack' ) ) {
		echo ' sc_stack_section_on';
	}
?>"
		<?php
		$jacqueline_css      = '';
		$jacqueline_bg_image = jacqueline_get_theme_option( 'front_page_features_bg_image' );
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
	$jacqueline_anchor_icon = jacqueline_get_theme_option( 'front_page_features_anchor_icon' );
	$jacqueline_anchor_text = jacqueline_get_theme_option( 'front_page_features_anchor_text' );
if ( ( ! empty( $jacqueline_anchor_icon ) || ! empty( $jacqueline_anchor_text ) ) && shortcode_exists( 'trx_sc_anchor' ) ) {
	echo do_shortcode(
		'[trx_sc_anchor id="front_page_section_features"'
									. ( ! empty( $jacqueline_anchor_icon ) ? ' icon="' . esc_attr( $jacqueline_anchor_icon ) . '"' : '' )
									. ( ! empty( $jacqueline_anchor_text ) ? ' title="' . esc_attr( $jacqueline_anchor_text ) . '"' : '' )
									. ']'
	);
}
?>
	<div class="front_page_section_inner front_page_section_features_inner
	<?php
	if ( jacqueline_get_theme_option( 'front_page_features_fullheight' ) ) {
		echo ' jacqueline-full-height sc_layouts_flex sc_layouts_columns_middle';
	}
	?>
			"
			<?php
			$jacqueline_css      = '';
			$jacqueline_bg_mask  = jacqueline_get_theme_option( 'front_page_features_bg_mask' );
			$jacqueline_bg_color_type = jacqueline_get_theme_option( 'front_page_features_bg_color_type' );
			if ( 'custom' == $jacqueline_bg_color_type ) {
				$jacqueline_bg_color = jacqueline_get_theme_option( 'front_page_features_bg_color' );
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
		<div class="front_page_section_content_wrap front_page_section_features_content_wrap content_wrap">
			<?php
			// Caption
			$jacqueline_caption = jacqueline_get_theme_option( 'front_page_features_caption' );
			if ( ! empty( $jacqueline_caption ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
				?>
				<h2 class="front_page_section_caption front_page_section_features_caption front_page_block_<?php echo ! empty( $jacqueline_caption ) ? 'filled' : 'empty'; ?>"><?php echo wp_kses( $jacqueline_caption, 'jacqueline_kses_content' ); ?></h2>
				<?php
			}

			// Description (text)
			$jacqueline_description = jacqueline_get_theme_option( 'front_page_features_description' );
			if ( ! empty( $jacqueline_description ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
				?>
				<div class="front_page_section_description front_page_section_features_description front_page_block_<?php echo ! empty( $jacqueline_description ) ? 'filled' : 'empty'; ?>"><?php echo wp_kses( wpautop( $jacqueline_description ), 'jacqueline_kses_content' ); ?></div>
				<?php
			}

			// Content (widgets)
			?>
			<div class="front_page_section_output front_page_section_features_output">
				<?php
				if ( is_active_sidebar( 'front_page_features_widgets' ) ) {
					dynamic_sidebar( 'front_page_features_widgets' );
				} elseif ( current_user_can( 'edit_theme_options' ) ) {
					if ( ! jacqueline_exists_trx_addons() ) {
						jacqueline_customizer_need_trx_addons_message();
					} else {
						jacqueline_customizer_need_widgets_message( 'front_page_features_caption', 'ThemeREX Addons - Services' );
					}
				}
				?>
			</div>
		</div>
	</div>
</div>
