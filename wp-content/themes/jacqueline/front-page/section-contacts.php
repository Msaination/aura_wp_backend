<div class="front_page_section front_page_section_contacts<?php
	$jacqueline_scheme = jacqueline_get_theme_option( 'front_page_contacts_scheme' );
	if ( ! empty( $jacqueline_scheme ) && ! jacqueline_is_inherit( $jacqueline_scheme ) ) {
		echo ' scheme_' . esc_attr( $jacqueline_scheme );
	}
	echo ' front_page_section_paddings_' . esc_attr( jacqueline_get_theme_option( 'front_page_contacts_paddings' ) );
	if ( jacqueline_get_theme_option( 'front_page_contacts_stack' ) ) {
		echo ' sc_stack_section_on';
	}
?>"
		<?php
		$jacqueline_css      = '';
		$jacqueline_bg_image = jacqueline_get_theme_option( 'front_page_contacts_bg_image' );
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
	$jacqueline_anchor_icon = jacqueline_get_theme_option( 'front_page_contacts_anchor_icon' );
	$jacqueline_anchor_text = jacqueline_get_theme_option( 'front_page_contacts_anchor_text' );
if ( ( ! empty( $jacqueline_anchor_icon ) || ! empty( $jacqueline_anchor_text ) ) && shortcode_exists( 'trx_sc_anchor' ) ) {
	echo do_shortcode(
		'[trx_sc_anchor id="front_page_section_contacts"'
									. ( ! empty( $jacqueline_anchor_icon ) ? ' icon="' . esc_attr( $jacqueline_anchor_icon ) . '"' : '' )
									. ( ! empty( $jacqueline_anchor_text ) ? ' title="' . esc_attr( $jacqueline_anchor_text ) . '"' : '' )
									. ']'
	);
}
?>
	<div class="front_page_section_inner front_page_section_contacts_inner
	<?php
	if ( jacqueline_get_theme_option( 'front_page_contacts_fullheight' ) ) {
		echo ' jacqueline-full-height sc_layouts_flex sc_layouts_columns_middle';
	}
	?>
			"
			<?php
			$jacqueline_css      = '';
			$jacqueline_bg_mask  = jacqueline_get_theme_option( 'front_page_contacts_bg_mask' );
			$jacqueline_bg_color_type = jacqueline_get_theme_option( 'front_page_contacts_bg_color_type' );
			if ( 'custom' == $jacqueline_bg_color_type ) {
				$jacqueline_bg_color = jacqueline_get_theme_option( 'front_page_contacts_bg_color' );
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
		<div class="front_page_section_content_wrap front_page_section_contacts_content_wrap content_wrap">
			<?php

			// Title and description
			$jacqueline_caption     = jacqueline_get_theme_option( 'front_page_contacts_caption' );
			$jacqueline_description = jacqueline_get_theme_option( 'front_page_contacts_description' );
			if ( ! empty( $jacqueline_caption ) || ! empty( $jacqueline_description ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
				// Caption
				if ( ! empty( $jacqueline_caption ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
					?>
					<h2 class="front_page_section_caption front_page_section_contacts_caption front_page_block_<?php echo ! empty( $jacqueline_caption ) ? 'filled' : 'empty'; ?>">
					<?php
						echo wp_kses( $jacqueline_caption, 'jacqueline_kses_content' );
					?>
					</h2>
					<?php
				}

				// Description
				if ( ! empty( $jacqueline_description ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
					?>
					<div class="front_page_section_description front_page_section_contacts_description front_page_block_<?php echo ! empty( $jacqueline_description ) ? 'filled' : 'empty'; ?>">
					<?php
						echo wp_kses( wpautop( $jacqueline_description ), 'jacqueline_kses_content' );
					?>
					</div>
					<?php
				}
			}

			// Content (text)
			$jacqueline_content = jacqueline_get_theme_option( 'front_page_contacts_content' );
			$jacqueline_layout  = jacqueline_get_theme_option( 'front_page_contacts_layout' );
			if ( 'columns' == $jacqueline_layout && ( ! empty( $jacqueline_content ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) ) {
				?>
				<div class="front_page_section_columns front_page_section_contacts_columns columns_wrap">
					<div class="column-1_3">
				<?php
			}

			if ( ( ! empty( $jacqueline_content ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) ) {
				?>
				<div class="front_page_section_content front_page_section_contacts_content front_page_block_<?php echo ! empty( $jacqueline_content ) ? 'filled' : 'empty'; ?>">
					<?php
					echo wp_kses( $jacqueline_content, 'jacqueline_kses_content' );
					?>
				</div>
				<?php
			}

			if ( 'columns' == $jacqueline_layout && ( ! empty( $jacqueline_content ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) ) {
				?>
				</div><div class="column-2_3">
				<?php
			}

			// Shortcode output
			$jacqueline_sc = jacqueline_get_theme_option( 'front_page_contacts_shortcode' );
			if ( ! empty( $jacqueline_sc ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
				?>
				<div class="front_page_section_output front_page_section_contacts_output front_page_block_<?php echo ! empty( $jacqueline_sc ) ? 'filled' : 'empty'; ?>">
					<?php
					jacqueline_show_layout( do_shortcode( $jacqueline_sc ) );
					?>
				</div>
				<?php
			}

			if ( 'columns' == $jacqueline_layout && ( ! empty( $jacqueline_content ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) ) {
				?>
				</div></div>
				<?php
			}
			?>

		</div>
	</div>
</div>
