<?php
$jacqueline_woocommerce_sc = jacqueline_get_theme_option( 'front_page_woocommerce_products' );
if ( ! empty( $jacqueline_woocommerce_sc ) ) {
	?><div class="front_page_section front_page_section_woocommerce<?php
		$jacqueline_scheme = jacqueline_get_theme_option( 'front_page_woocommerce_scheme' );
		if ( ! empty( $jacqueline_scheme ) && ! jacqueline_is_inherit( $jacqueline_scheme ) ) {
			echo ' scheme_' . esc_attr( $jacqueline_scheme );
		}
		echo ' front_page_section_paddings_' . esc_attr( jacqueline_get_theme_option( 'front_page_woocommerce_paddings' ) );
		if ( jacqueline_get_theme_option( 'front_page_woocommerce_stack' ) ) {
			echo ' sc_stack_section_on';
		}
	?>"
			<?php
			$jacqueline_css      = '';
			$jacqueline_bg_image = jacqueline_get_theme_option( 'front_page_woocommerce_bg_image' );
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
		$jacqueline_anchor_icon = jacqueline_get_theme_option( 'front_page_woocommerce_anchor_icon' );
		$jacqueline_anchor_text = jacqueline_get_theme_option( 'front_page_woocommerce_anchor_text' );
		if ( ( ! empty( $jacqueline_anchor_icon ) || ! empty( $jacqueline_anchor_text ) ) && shortcode_exists( 'trx_sc_anchor' ) ) {
			echo do_shortcode(
				'[trx_sc_anchor id="front_page_section_woocommerce"'
											. ( ! empty( $jacqueline_anchor_icon ) ? ' icon="' . esc_attr( $jacqueline_anchor_icon ) . '"' : '' )
											. ( ! empty( $jacqueline_anchor_text ) ? ' title="' . esc_attr( $jacqueline_anchor_text ) . '"' : '' )
											. ']'
			);
		}
	?>
		<div class="front_page_section_inner front_page_section_woocommerce_inner
			<?php
			if ( jacqueline_get_theme_option( 'front_page_woocommerce_fullheight' ) ) {
				echo ' jacqueline-full-height sc_layouts_flex sc_layouts_columns_middle';
			}
			?>
				"
				<?php
				$jacqueline_css      = '';
				$jacqueline_bg_mask  = jacqueline_get_theme_option( 'front_page_woocommerce_bg_mask' );
				$jacqueline_bg_color_type = jacqueline_get_theme_option( 'front_page_woocommerce_bg_color_type' );
				if ( 'custom' == $jacqueline_bg_color_type ) {
					$jacqueline_bg_color = jacqueline_get_theme_option( 'front_page_woocommerce_bg_color' );
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
			<div class="front_page_section_content_wrap front_page_section_woocommerce_content_wrap content_wrap woocommerce">
				<?php
				// Content wrap with title and description
				$jacqueline_caption     = jacqueline_get_theme_option( 'front_page_woocommerce_caption' );
				$jacqueline_description = jacqueline_get_theme_option( 'front_page_woocommerce_description' );
				if ( ! empty( $jacqueline_caption ) || ! empty( $jacqueline_description ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
					// Caption
					if ( ! empty( $jacqueline_caption ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
						?>
						<h2 class="front_page_section_caption front_page_section_woocommerce_caption front_page_block_<?php echo ! empty( $jacqueline_caption ) ? 'filled' : 'empty'; ?>">
						<?php
							echo wp_kses( $jacqueline_caption, 'jacqueline_kses_content' );
						?>
						</h2>
						<?php
					}

					// Description (text)
					if ( ! empty( $jacqueline_description ) || ( current_user_can( 'edit_theme_options' ) && is_customize_preview() ) ) {
						?>
						<div class="front_page_section_description front_page_section_woocommerce_description front_page_block_<?php echo ! empty( $jacqueline_description ) ? 'filled' : 'empty'; ?>">
						<?php
							echo wp_kses( wpautop( $jacqueline_description ), 'jacqueline_kses_content' );
						?>
						</div>
						<?php
					}
				}

				// Content (widgets)
				?>
				<div class="front_page_section_output front_page_section_woocommerce_output list_products shop_mode_thumbs">
					<?php
					if ( 'products' == $jacqueline_woocommerce_sc ) {
						$jacqueline_woocommerce_sc_ids      = jacqueline_get_theme_option( 'front_page_woocommerce_products_per_page' );
						$jacqueline_woocommerce_sc_per_page = count( explode( ',', $jacqueline_woocommerce_sc_ids ) );
					} else {
						$jacqueline_woocommerce_sc_per_page = max( 1, (int) jacqueline_get_theme_option( 'front_page_woocommerce_products_per_page' ) );
					}
					$jacqueline_woocommerce_sc_columns = max( 1, min( $jacqueline_woocommerce_sc_per_page, (int) jacqueline_get_theme_option( 'front_page_woocommerce_products_columns' ) ) );
					echo do_shortcode(
						"[{$jacqueline_woocommerce_sc}"
										. ( 'products' == $jacqueline_woocommerce_sc
												? ' ids="' . esc_attr( $jacqueline_woocommerce_sc_ids ) . '"'
												: '' )
										. ( 'product_category' == $jacqueline_woocommerce_sc
												? ' category="' . esc_attr( jacqueline_get_theme_option( 'front_page_woocommerce_products_categories' ) ) . '"'
												: '' )
										. ( 'best_selling_products' != $jacqueline_woocommerce_sc
												? ' orderby="' . esc_attr( jacqueline_get_theme_option( 'front_page_woocommerce_products_orderby' ) ) . '"'
													. ' order="' . esc_attr( jacqueline_get_theme_option( 'front_page_woocommerce_products_order' ) ) . '"'
												: '' )
										. ' per_page="' . esc_attr( $jacqueline_woocommerce_sc_per_page ) . '"'
										. ' columns="' . esc_attr( $jacqueline_woocommerce_sc_columns ) . '"'
						. ']'
					);
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}
