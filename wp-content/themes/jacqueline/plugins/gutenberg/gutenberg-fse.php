<?php
/**
 * Gutenberg Full-Site Editor (FSE) support functions.
 */

if ( ! defined( 'JACQUELINE_FSE_TEMPLATE_PART_PT' ) ) define( 'JACQUELINE_FSE_TEMPLATE_PART_PT', 'wp_template_part' );

if ( ! defined( 'JACQUELINE_FSE_TEMPLATE_PART_AREA_HEADER' ) )  define( 'JACQUELINE_FSE_TEMPLATE_PART_AREA_HEADER',  defined( 'WP_TEMPLATE_PART_AREA_HEADER' )  ? WP_TEMPLATE_PART_AREA_HEADER  : 'header' );
if ( ! defined( 'JACQUELINE_FSE_TEMPLATE_PART_AREA_FOOTER' ) )  define( 'JACQUELINE_FSE_TEMPLATE_PART_AREA_FOOTER',  defined( 'WP_TEMPLATE_PART_AREA_FOOTER' )  ? WP_TEMPLATE_PART_AREA_FOOTER  : 'footer' );
if ( ! defined( 'JACQUELINE_FSE_TEMPLATE_PART_AREA_SIDEBAR' ) ) define( 'JACQUELINE_FSE_TEMPLATE_PART_AREA_SIDEBAR', defined( 'WP_TEMPLATE_PART_AREA_SIDEBAR' ) ? WP_TEMPLATE_PART_AREA_SIDEBAR : 'sidebar' );
if ( ! defined( 'JACQUELINE_FSE_TEMPLATE_PART_AREA_UNCATEGORIZED' ) ) define( 'JACQUELINE_FSE_TEMPLATE_PART_AREA_UNCATEGORIZED', defined( 'WP_TEMPLATE_PART_AREA_UNCATEGORIZED' ) ? WP_TEMPLATE_PART_AREA_UNCATEGORIZED : 'uncategorized' );

// Load additional files with FSE support
require_once jacqueline_get_file_dir( 'plugins/gutenberg/gutenberg-fse-lists.php' );
require_once jacqueline_get_file_dir( 'plugins/gutenberg/gutenberg-fse-templates.php' );


if ( ! function_exists( 'jacqueline_gutenberg_fse_loader' ) ) {
	add_action( 'wp_loaded', 'jacqueline_gutenberg_fse_loader', 1 );
	/**
	 * Turn off a Gutenberg (plugin) templates on frontend
	 * if a theme-specific frontend editor is active.
	 */
	function jacqueline_gutenberg_fse_loader() {
		// 
		if ( jacqueline_exists_gutenberg_fse()
			&& get_option( 'show_on_front' ) == 'page'
			&& (int) get_option('page_on_front') > 0
			&& jacqueline_is_on( jacqueline_get_theme_option( 'front_page_enabled', false ) )
			&& jacqueline_get_current_url() == '/'
		) {
		 	remove_action( 'wp_loaded', 'gutenberg_add_template_loader_filters' );
		}
	}
}

if ( ! function_exists( 'jacqueline_gutenberg_is_fse_wp_support' ) ) {
	/**
	 * Check if WordPress with FSE support installed
	 * (WordPress 5.9+ Site Editor is present).
	 */
	function jacqueline_gutenberg_is_fse_wp_support() {
		return function_exists( 'wp_is_block_theme' );
	}
}

if ( ! function_exists( 'jacqueline_gutenberg_is_fse_gb_support' ) ) {
	/**
	 * Check if a plugin "Gutenberg" with FSE support is installed.
	 */
	function jacqueline_gutenberg_is_fse_gb_support() {
		return function_exists( 'gutenberg_is_fse_theme' );
	}
}

if ( ! function_exists( 'jacqueline_exists_gutenberg_fse' ) ) {
	/**
	 * Check if FSE is available
	 * (WordPress 5.9+ or the plugin "Gutenberg" are installed).
	 */
	function jacqueline_exists_gutenberg_fse() {
		return jacqueline_exists_gutenberg() && ( jacqueline_gutenberg_is_fse_wp_support() || jacqueline_gutenberg_is_fse_gb_support() );
	}
}

if ( ! function_exists( 'jacqueline_gutenberg_is_fse_theme' ) ) {
	/**
	 * Check if the current theme supports a FSE behaviour.
	 */
	function jacqueline_gutenberg_is_fse_theme() {
		static $fse = -1;
		if ( $fse == -1 ) {
			if ( jacqueline_gutenberg_is_fse_wp_support() ) {                                    // WordPress 5.9+ FSE
				$fse = (bool) wp_is_block_theme();
			} else if ( jacqueline_gutenberg_is_fse_gb_support() ) {                             // Plugin Gutenberg FSE
				$fse = (bool) gutenberg_is_fse_theme();
			} else {
				$fse = is_readable( JACQUELINE_THEME_DIR . 'templates/index.html' )              // WordPress 5.9+ FSE
						|| ( JACQUELINE_THEME_DIR != JACQUELINE_CHILD_DIR && is_readable( JACQUELINE_CHILD_DIR . 'templates/index.html' ) )
						|| is_readable( JACQUELINE_THEME_DIR . 'block-templates/index.html' )    // Plugin Gutenberg FSE
						|| ( JACQUELINE_THEME_DIR != JACQUELINE_CHILD_DIR && is_readable( JACQUELINE_CHILD_DIR . 'block-templates/index.html' ) );
			}
		}
		return $fse;
	}
}

if ( ! function_exists( 'jacqueline_gutenberg_is_fse_enabled' ) ) {
	/**
	 * Check if the current theme supports FSE behaviour and FSE is installed and activated
	 */
	function jacqueline_gutenberg_is_fse_enabled() {
		return jacqueline_exists_gutenberg_fse() && jacqueline_gutenberg_is_fse_theme();
	}
}

if ( ! function_exists( 'jacqueline_gutenberg_fse_skin_updated' ) ) {
	add_action( 'jacqueline_action_theme_updated', 'jacqueline_gutenberg_fse_skin_updated' );
	add_action( 'jacqueline_action_skin_updated', 'jacqueline_gutenberg_fse_skin_updated', 10, 1 );
	/**
	 * Copy FSE-required folders from an updated skin's subfolder '_fse' to the theme's root folder.
	 * 
	 * @hooked 'jacqueline_action_theme_updated'
	 * @hooked 'jacqueline_action_skin_updated'
	 */
	function jacqueline_gutenberg_fse_skin_updated( $skin = '' ) {
		// Get an active skin name if empty
		if ( current_action() == 'jacqueline_action_theme_updated' && empty( $skin ) ) {
			$skin = jacqueline_skins_get_active_skin_name();
		}
		if ( jacqueline_skins_get_current_skin_name() == $skin ) {
			jacqueline_gutenberg_fse_copy_from_skin( $skin );
			jacqueline_gutenberg_fse_update_theme_json( $skin );
		}
	}
}

if ( ! function_exists( 'jacqueline_gutenberg_fse_copy_from_skin' ) ) {
	add_action( 'jacqueline_action_skin_switched', 'jacqueline_gutenberg_fse_copy_from_skin', 10, 2 );
	/**
	 * Copy FSE-required folders from a new skin's subfolder '_fse' to the theme's root folder.
	 * 
	 * @hooked 'jacqueline_action_skin_switched'
	 */
	function jacqueline_gutenberg_fse_copy_from_skin( $new_skin, $old_skin = '' ) {
		$theme_templates_dir = jacqueline_prepare_path( JACQUELINE_THEME_DIR . ( jacqueline_gutenberg_is_fse_wp_support() ? 'templates/' : 'block-templates/' ) );
		$theme_template_parts_dir = jacqueline_prepare_path( JACQUELINE_THEME_DIR . ( jacqueline_gutenberg_is_fse_wp_support() ? 'parts/' : 'block-template-parts/' ) );
		$theme_json = jacqueline_prepare_path( JACQUELINE_THEME_DIR . 'theme.json' );
		$skin_templates_dir = jacqueline_prepare_path( JACQUELINE_THEME_DIR . 'skins/' . $new_skin . '/_fse' . ( jacqueline_gutenberg_is_fse_wp_support() ? '/templates/' : '/block-templates/' ) );
		$skin_template_parts_dir = jacqueline_prepare_path( JACQUELINE_THEME_DIR . 'skins/' . $new_skin . '/_fse' . ( jacqueline_gutenberg_is_fse_wp_support() ? '/parts/' : '/block-template-parts/' ) );
		// Remove old templates from the stylesheet dir (if exists)
		if ( is_dir( $theme_templates_dir ) ) {
			jacqueline_unlink( $theme_templates_dir );
		}
		if ( is_dir( $theme_template_parts_dir ) ) {
			jacqueline_unlink( $theme_template_parts_dir );
		}
		if ( file_exists( $theme_json ) ) {
			jacqueline_unlink( $theme_json );
		}
		// If a new skin is a FSE compatible - copy two folders with block templates and a file 'theme.json'
		// from a new skin's subfolder '_fse' to the theme's root folder
		if ( is_dir( $skin_templates_dir ) ) {
			jacqueline_copy( $skin_templates_dir, $theme_templates_dir );
		}
		if ( is_dir( $skin_template_parts_dir ) ) {
			jacqueline_copy( $skin_template_parts_dir, $theme_template_parts_dir );
		}
	}
}

if ( ! function_exists( 'jacqueline_gutenberg_fse_update_theme_json' ) ) {
	add_action( 'jacqueline_action_skin_switched', 'jacqueline_gutenberg_fse_update_theme_json', 30, 2 );
	add_action( 'jacqueline_action_save_options', 'jacqueline_gutenberg_fse_update_theme_json', 30 );
	add_action( 'trx_addons_action_save_options', 'jacqueline_gutenberg_fse_update_theme_json', 30 );
	/**
	 * Update the file 'theme.json' after the current skin is switched and/or theme options are saved.
	 * 
	 * @hooked 'jacqueline_action_skin_switched', 30
	 * @hooked 'jacqueline_action_save_options', 30
	 * @hooked 'trx_addons_action_save_options', 30
	 * 
	 * Trigger filter 'jacqueline_filter_gutenberg_fse_theme_json_data' to allow other modules modify this data before saving.
	 */
	function jacqueline_gutenberg_fse_update_theme_json( $new_skin = '', $old_skin = '' ) {
		// Get an active skin name if empty
		if ( in_array( current_action(), array( 'jacqueline_action_save_options', 'trx_addons_action_save_options' ) ) && empty( $new_skin ) ) {
			$new_skin = jacqueline_skins_get_active_skin_name();
		}
		// If the current skin supports FSE
		if ( ! empty( $new_skin ) ) {	// && is_dir( JACQUELINE_THEME_DIR . jacqueline_skins_get_current_skin_dir( $new_skin ) . '_fse' ) ) {
			$theme_json_data = jacqueline_gutenberg_fse_theme_json_data();
			if ( ! empty( $theme_json_data ) && is_array( $theme_json_data ) ) {
				jacqueline_fpc( jacqueline_prepare_path( JACQUELINE_THEME_DIR . 'theme.json' ), json_encode( $theme_json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			}
		}
	}
}

if ( ! function_exists( 'jacqueline_gutenberg_fse_theme_json_data' ) ) {
	/**
	 * Return a default data to save to the file 'theme.json'
	 * 
	 * Trigger filter 'jacqueline_filter_gutenberg_fse_theme_json_data' to allow other modules change data.
	 * 
	 * @return array  Data for the 'theme.json'
	 */
	function jacqueline_gutenberg_fse_theme_json_data() {

		$data = array(

					"\$schema" => "https://schemas.wp.org/trunk/theme.json",

					"version" => 2,
					
					// General settings
					"settings" => array(

						"appearanceTools" => true,

						"spacing" => array(
							"units" => array( "%", "px", "em", "rem", "vh", "vw" ),
							"blockGap" => null,
						),

						// Theme colors
						"color" => array(
							"palette"   => array(),		// Should be added later from a color scheme
							"duotone"   => array(),		// Should be added later from a color scheme
							"gradients" => array(),		// Should be added later from a color scheme
						),

						// Typography
						"typography" => array(
							"fluid"   =>  true,
							"dropCap" => true,
						),

						// Layout dimensions
						// Should be added later from a theme vars
						"layout" => array(),

						// Custom styles - can be used below as CSS-vars.
						"custom" => array(
							// Spacing - can be used as a value for a padding or a margin
							// For example: var(--wp--custom--spacing--xxx), where 'xxx' is a key of the array 'spacing'
							"spacing" => array(
								"tiny"   => "var(--sc-space-tiny,   1rem)",      //"max( 1rem, 3vw )",
								"small"  => "var(--sc-space-small,  2rem)",      //"max( 1.25rem, 5vw )",
								"medium" => "var(--sc-space-medium, 3.3333rem)", //"clamp( 2rem, 8vw, calc( 4 * var(--wp--style--block-gap) ) )",
								"large"  => "var(--sc-space-large,  6.6667rem)", //"clamp( 4rem, 10vw, 8rem )",
								"huge"   => "var(--sc-space-huge,   8.6667rem)",
							),
						),
					),

					// General styles
					"styles" => array(
						// Gap between blocks
						"spacing" => array(
							"blockGap" => 0,
						),
						// Core blocks decoration
						"blocks" => array(
							"core/button" => array(
								"border" => array(
									"radius" => "0",
								),
								"color" => array(
									"background" => "var(--theme-color-text_link)",
									"text"       => "var(--theme-color-inverse_link)",
								),
								"typography" => array(
									"fontFamily" => "var(--theme-font-button_font-family)",
									"fontWeight" => "var(--theme-font-button_font-weight)",
									"fontSize"   => "var(--theme-font-button_font-size)",
									"lineHeight" => "var(--theme-font-button_line-height)",
								),
							),
							"core/post-comments" => array(
								"spacing" => array(
									"padding" => array(
										"top" => "var(--wp--custom--spacing--small)",
									),
								),
							),
							"core/pullquote" => array(
								"border" => array(
									"width" => "1px 0",
								),
							),
							"core/quote" => array(
								"border" => array(
									"width" => "1px",
								),
							),
						),
					),
				);

		// Add palette: all colors from the scheme 'default'
		$scheme = jacqueline_get_scheme_colors();
		$groups = jacqueline_storage_get( 'scheme_color_groups' );
		$names  = jacqueline_storage_get( 'scheme_color_names' );
		$prefix = apply_filters( 'jacqueline_filter_gutenberg_fse_theme_json_data_add_scheme_color_prefix', false ) ? 'theme-color-' : '';
		foreach( $groups as $g => $group ) {
			foreach( $names as $n => $name ) {
				$c = 'main' == $g ? ( 'text' == $n ? 'text_color' : $n ) : $g . '_' . str_replace( 'text_', '', $n );
				if ( isset( $scheme[ $c ] ) ) {
					$data['settings']['color']['palette'][] = array(
						'slug'  => $prefix . preg_replace( '/([a-z])([0-9])+/', '$1-$2', str_replace( '_', '-', $c ) ),
						'name'  => ( 'main' == $g ? '' : $group['title'] . ' ' ) . $name['title'],
						'color' => $scheme[ $c ],
					);
				}
			}
			// Add only one group of colors
			// Delete next condition (or add false && to them) to add all groups
			if ( 'main' == $g ) {
				break;
			}
		}

		// Add duotones (a two colors combination)
		if ( ! empty( $scheme[ jacqueline_get_scheme_color_name( 'bg_color' ) ] ) && ! empty( $scheme[ jacqueline_get_scheme_color_name( 'text' ) ] ) ) {
			$data['settings']['color']['duotone'][] = array(
				"colors" => array( $scheme[ jacqueline_get_scheme_color_name( 'bg_color' ) ], $scheme[ jacqueline_get_scheme_color_name( 'text' ) ] ),
				"slug"   => 'bg-and-text',
				"name"   => esc_html__( 'Background and text color', 'jacqueline' )
			);
		}
		if ( ! empty( $scheme[ jacqueline_get_scheme_color_name( 'bg_color' ) ] ) && ! empty( $scheme[ jacqueline_get_scheme_color_name( 'text_dark' ) ] ) ) {
			$data['settings']['color']['duotone'][] = array(
				"colors" => array( $scheme[ jacqueline_get_scheme_color_name( 'bg_color' ) ], $scheme[ jacqueline_get_scheme_color_name( 'text_dark' ) ] ),
				"slug"   => 'bg-and-dark',
				"name"   => esc_html__( 'Background and dark color', 'jacqueline' )
			);
		}
		if ( ! empty( $scheme[ jacqueline_get_scheme_color_name( 'bg_color' ) ] ) && ! empty( $scheme[ jacqueline_get_scheme_color_name( 'text_link' ) ] ) ) {
			$data['settings']['color']['duotone'][] = array(
				"colors" => array( $scheme[ jacqueline_get_scheme_color_name( 'bg_color' ) ], $scheme[ jacqueline_get_scheme_color_name( 'text_link' ) ] ),
				"slug"   => 'bg-and-link',
				"name"   => esc_html__( 'Background and link color', 'jacqueline' )
			);
		}

		// Add gradients
		if ( ! empty( $scheme[ jacqueline_get_scheme_color_name( 'text_hover' ) ] ) && ! empty( $scheme[ jacqueline_get_scheme_color_name( 'text_link' ) ] ) ) {
			$data['settings']['color']['gradients'][] = array(
				"slug"     => 'vertical-link-to-hover',
				"gradient" => 'linear-gradient(to bottom,var(--theme-color-' . jacqueline_get_scheme_color_name( 'text_link' ) . ') 0%,var(--theme-color-' . jacqueline_get_scheme_color_name( 'text_hover' ) . ') 100%)',
				"name"     => esc_html__( 'Vertical from link color to hover color', 'jacqueline' ),
			);
			$data['settings']['color']['gradients'][] = array(
				"slug"     => 'diagonal-link-to-hover',
				"gradient" => 'linear-gradient(to bottom right,var(--theme-color-' . jacqueline_get_scheme_color_name( 'text_link' ) . ') 0%,var(--theme-color-' . jacqueline_get_scheme_color_name( 'text_hover' ) . ') 100%)',
				"name"     => esc_html__( 'Diagonal from link color to hover color', 'jacqueline' ),
			);
		}

		// Add fonts
		$fonts = jacqueline_get_theme_fonts();
		$added = array();
		foreach( $fonts as $tag => $font ) {
			if ( ! in_array( $font['font-family'], $added ) ) {
				$added[] = $font['font-family'];
				$data['settings']['typography']['fontFamilies'][] = array(
					"fontFamily" => $font['font-family'],
					"name"       => $font['font-family'], //$font['title'],
					"slug"       => "{$tag}-font",
				);
			}
		}

		// Layout dimensions
		$vars = jacqueline_get_theme_vars();
		$data['settings']['layout']['contentSize'] = ( $vars['page_width'] - $vars['sidebar_width'] - $vars['sidebar_gap'] ) . 'px';
		$data['settings']['layout']['wideSize']    = $vars['page_width'] . 'px';

		// Add templates for FSE themes
		if ( jacqueline_gutenberg_is_fse_theme() ) {
			// Theme-specific templates for posts, pages, etc.
			$data['customTemplates'] = array(
				array(
					"name"      => "blog",
					"title"     => esc_html__( "Blog archive", 'jacqueline' ),
					"postTypes" => array(
						"page"
					)
				)
			);

			// Theme-specific template parts
			$data['templateParts'] = array(
				array(
					"name"  => "header",
					"title" => esc_html__( "Header for FSE", 'jacqueline' ),
					"area"  => JACQUELINE_FSE_TEMPLATE_PART_AREA_HEADER,
				),
				array(
					"name"  => "sidebar",
					"title" => esc_html__( "Sidebar for FSE", 'jacqueline' ),
					"area"  => JACQUELINE_FSE_TEMPLATE_PART_AREA_SIDEBAR,
				),
				array(
					"name"  => "content-404",
					"title" => esc_html__( "Page 404 content", 'jacqueline' ),
					"area"  => WP_TEMPLATE_PART_AREA_UNCATEGORIZED,
				),
				array(
					"name"  => "none-archive",
					"title" => esc_html__( "No posts found", 'jacqueline' ),
					"area"  => WP_TEMPLATE_PART_AREA_UNCATEGORIZED,
				),
				array(
					"name"  => "none-search",
					"title" => esc_html__( "Nothing matched search", 'jacqueline' ),
					"area"  => WP_TEMPLATE_PART_AREA_UNCATEGORIZED,
				),
				array(
					"name"  => "blog-post-standard",
					"title" => esc_html__( "Blog post item: Standard", 'jacqueline' ),
					"area"  => WP_TEMPLATE_PART_AREA_UNCATEGORIZED,
				),
				array(
					"name"  => "blog-post-header",
					"title" => esc_html__( "Blog post header", 'jacqueline' ),
					"area"  => WP_TEMPLATE_PART_AREA_UNCATEGORIZED,
				),
				array(
					"name"  => "blog-pagination",
					"title" => esc_html__( "Blog pagination", 'jacqueline' ),
					"area"  => WP_TEMPLATE_PART_AREA_UNCATEGORIZED,
				),
				array(
					"name"  => "footer",
					"title" => esc_html__( "Footer for FSE", 'jacqueline' ),
					"area"  => WP_TEMPLATE_PART_AREA_FOOTER,
				),
			);
		}

		return apply_filters( 'jacqueline_filter_gutenberg_fse_theme_json_data', $data );
	}
}
