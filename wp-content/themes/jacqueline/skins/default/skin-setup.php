<?php
/**
 * Skin Setup
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.76.0
 */


//--------------------------------------------
// SKIN DEFAULTS
//--------------------------------------------

// Return theme's (skin's) default value for the specified parameter
if ( ! function_exists( 'jacqueline_theme_defaults' ) ) {
	function jacqueline_theme_defaults( $name = '', $value = '' ) {
		$defaults = array(
			'page_width'          => 1290,
			'page_boxed_extra'  => 60,
			'page_fullwide_max' => 1920,
			'page_fullwide_extra' => 60,
			'sidebar_width'       => 410,
			'sidebar_gap'       => 40,
			'grid_gap'          => 30,
			'rad'               => 0
		);
		if ( empty( $name ) ) {
			return $defaults;
		} else {
			if ( $value === '' && isset( $defaults[ $name ] ) ) {
				$value = $defaults[ $name ];
			}
			return $value;
		}
	}
}


// WOOCOMMERCE SETUP
//--------------------------------------------------

// Allow extended layouts for WooCommerce
if ( ! function_exists( 'jacqueline_skin_woocommerce_allow_extensions' ) ) {
	add_filter( 'jacqueline_filter_load_woocommerce_extensions', 'jacqueline_skin_woocommerce_allow_extensions' );
	function jacqueline_skin_woocommerce_allow_extensions( $allow ) {
		return false;
	}
}


// Theme init priorities:
// Action 'after_setup_theme'
// 1 - register filters to add/remove lists items in the Theme Options
// 2 - create Theme Options
// 3 - add/remove Theme Options elements
// 5 - load Theme Options. Attention! After this step you can use only basic options (not overriden)
// 9 - register other filters (for installer, etc.)
//10 - standard Theme init procedures (not ordered)
// Action 'wp_loaded'
// 1 - detect override mode. Attention! Only after this step you can use overriden options (separate values for the shop, courses, etc.)


//--------------------------------------------
// SKIN SETTINGS
//--------------------------------------------
if ( ! function_exists( 'jacqueline_skin_setup' ) ) {
	add_action( 'after_setup_theme', 'jacqueline_skin_setup', 1 );
	function jacqueline_skin_setup() {

		$GLOBALS['JACQUELINE_STORAGE'] = array_merge( $GLOBALS['JACQUELINE_STORAGE'], array(

			// Key validator: market[env|loc]-vendor[axiom|ancora|themerex]
			'theme_pro_key'       => 'env-themerex',

			'theme_doc_url'       => '//jacqueline.themerex.net/doc/',

			'theme_demofiles_url' => '//demofiles.themerex.net/jacqueline-new/',
			
			'theme_rate_url'      => '//themeforest.net/downloads',

			'theme_custom_url'    => '//themerex.net/offers/?utm_source=offers&utm_medium=click&utm_campaign=themeinstall',

			'theme_support_url'   => '//themerex.net/support/',

            'theme_download_url'  => '//themeforest.net/item/jacqueline-spa-massage-salon-theme/17101639',            // ThemeREX

            'theme_video_url'     => '//www.youtube.com/channel/UCnFisBimrK2aIE-hnY70kCA',   // ThemeREX

            'theme_privacy_url'   => '//themerex.net/privacy-policy/',                       // ThemeREX

            'portfolio_url'       => '//themeforest.net/user/themerex/portfolio',            // ThemeREX

			// Comma separated slugs of theme-specific categories (for get relevant news in the dashboard widget)
			// (i.e. 'children,kindergarten')
			'theme_categories'    => '',
		) );
	}
}


// Add/remove/change Theme Settings
if ( ! function_exists( 'jacqueline_skin_setup_settings' ) ) {
	add_action( 'after_setup_theme', 'jacqueline_skin_setup_settings', 1 );
	function jacqueline_skin_setup_settings() {
		// Example: enable (true) / disable (false) thumbs in the prev/next navigation
		jacqueline_storage_set_array( 'settings', 'thumbs_in_navigation', false );
	}
}



//--------------------------------------------
// SKIN FONTS
//--------------------------------------------
if ( ! function_exists( 'jacqueline_skin_setup_fonts' ) ) {
	add_action( 'after_setup_theme', 'jacqueline_skin_setup_fonts', 1 );
	function jacqueline_skin_setup_fonts() {
		// Fonts to load when theme start
		// It can be:
		// - Google fonts (specify name, family and styles)
		// - Adobe fonts (specify name, family and link URL)
		// - uploaded fonts (specify name, family), placed in the folder css/font-face/font-name inside the skin folder
		// Attention! Font's folder must have name equal to the font's name, with spaces replaced on the dash '-'
		// example: font name 'TeX Gyre Termes', folder 'TeX-Gyre-Termes'
		jacqueline_storage_set(
			'load_fonts', array(
				array(
					'name'   => 'droid-serif',
					'family' => 'serif',
					'link'   => 'https://use.typekit.net/xxq3pfj.css',
					'styles' => ''
				),
				// Google font
				array(
					'name'   => 'Raleway',
					'family' => 'sans-serif',
					'link'   => '',
					'styles' => 'ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700',     // Parameter 'style' used only for the Google fonts
				),
                array(
                    'name'   => 'Mr De Haviland',
                    'family' => 'cursive',
                    'link'   => '',
                    'styles' => 'ital,wght@0,400',                                                                 // Parameter 'style' used only for the Google fonts
                ),
			)
		);

		// Characters subset for the Google fonts. Available values are: latin,latin-ext,cyrillic,cyrillic-ext,greek,greek-ext,vietnamese
		jacqueline_storage_set( 'load_fonts_subset', 'latin,latin-ext' );

		// Settings of the main tags.
		// Default value of 'font-family' may be specified as reference to the array $load_fonts (see above)
		// or as comma-separated string.
		// In the second case (if 'font-family' is specified manually as comma-separated string):
		//    1) Font name with spaces in the parameter 'font-family' will be enclosed in the quotes and no spaces after comma!
		//    2) If font-family inherit a value from the 'Main text' - specify 'inherit' as a value
		// example:
		// Correct:   'font-family' => jacqueline_get_load_fonts_family_string( $load_fonts[0] )
		// Correct:   'font-family' => 'Roboto,sans-serif'
		// Correct:   'font-family' => '"PT Serif",sans-serif'
		// Incorrect: 'font-family' => 'Roboto, sans-serif'
		// Incorrect: 'font-family' => 'PT Serif,sans-serif'

		$font_description = esc_html__( 'Please use only the following units: "rem" or "em".', 'jacqueline' )
							. ( is_customize_preview() ? '<br>' . esc_html__( 'Press "Reload preview area" button at the top of this panel after the all font parameters are changed.', 'jacqueline' ) : '' );

		jacqueline_storage_set(
			'theme_fonts', array(
				'p'       => array(
					'title'           => esc_html__( 'Main text', 'jacqueline' ),
					'description'     => sprintf( $font_description, esc_html__( 'main text', 'jacqueline' ) ),
					'font-family'     => 'droid-serif,serif',
					'font-size'       => '1rem',
					'font-weight'     => '400',
					'font-style'      => 'normal',
					'line-height'     => '1.65em',
					'text-decoration' => 'none',
					'text-transform'  => 'none',
					'letter-spacing'  => '0px',
					'margin-top'      => '0em',
					'margin-bottom'   => '1.75em',
				),
				'post'    => array(
					'title'           => esc_html__( 'Article text', 'jacqueline' ),
					'description'     => sprintf( $font_description, esc_html__( 'article text', 'jacqueline' ) ),
					'font-family'     => 'inherit',		// Example: '"PR Serif",serif',
					'font-size'       => '',			// Example: '1.286rem',
					'font-weight'     => 'inherit',		// Example: '400',
					'font-style'      => 'inherit',		// Example: 'normal',
					'line-height'     => '',			// Example: '1.75em',
					'text-decoration' => 'inherit',		// Example: 'none',
					'text-transform'  => 'inherit',		// Example: 'none',
					'letter-spacing'  => '',			// Example: '',
					'margin-top'      => '',			// Example: '0em',
					'margin-bottom'   => '',			// Example: '1.4em',
				),
				'h1'      => array(
					'title'           => esc_html__( 'Heading 1', 'jacqueline' ),
					'description'     => sprintf( $font_description, esc_html__( 'tag H1', 'jacqueline' ) ),
					'font-family'     => 'Raleway,sans-serif',
					'font-size'       => '3.563em',
					'font-weight'     => '300',
					'font-style'      => 'normal',
					'line-height'     => '1em',
					'text-decoration' => 'none',
					'text-transform'  => 'uppercase',
					'letter-spacing'  => '2.85px',
					'margin-top'      => '1.04em',
					'margin-bottom'   => '0.46em',
				),
				'h2'      => array(
					'title'           => esc_html__( 'Heading 2', 'jacqueline' ),
					'description'     => sprintf( $font_description, esc_html__( 'tag H2', 'jacqueline' ) ),
					'font-family'     => 'Raleway,sans-serif',
					'font-size'       => '2.938em',
					'font-weight'     => '400',
					'font-style'      => 'normal',
					'line-height'     => '1.021em',
					'text-decoration' => 'none',
					'text-transform'  => 'uppercase',
					'letter-spacing'  => '4.7px',
					'margin-top'      => '0.67em',
					'margin-bottom'   => '0.56em',
				),
				'h3'      => array(
					'title'           => esc_html__( 'Heading 3', 'jacqueline' ),
					'description'     => sprintf( $font_description, esc_html__( 'tag H3', 'jacqueline' ) ),
					'font-family'     => 'Raleway,sans-serif',
					'font-size'       => '2.438em',
					'font-weight'     => '400',
					'font-style'      => 'normal',
					'line-height'     => '1.026em',
					'text-decoration' => 'none',
					'text-transform'  => 'uppercase',
					'letter-spacing'  => '1.95px',
					'margin-top'      => '0.94em',
					'margin-bottom'   => '0.72em',
				),
				'h4'      => array(
					'title'           => esc_html__( 'Heading 4', 'jacqueline' ),
					'description'     => sprintf( $font_description, esc_html__( 'tag H4', 'jacqueline' ) ),
					'font-family'     => 'Raleway,sans-serif',
					'font-size'       => '1.750em',
					'font-weight'     => '400',
					'font-style'      => 'normal',
					'line-height'     => '1.214em',
					'text-decoration' => 'none',
					'text-transform'  => 'uppercase',
					'letter-spacing'  => '2.1px',
					'margin-top'      => '1.15em',
					'margin-bottom'   => '0.83em',
				),
				'h5'      => array(
					'title'           => esc_html__( 'Heading 5', 'jacqueline' ),
					'description'     => sprintf( $font_description, esc_html__( 'tag H5', 'jacqueline' ) ),
					'font-family'     => 'Raleway,sans-serif',
					'font-size'       => '1.500em',
					'font-weight'     => '400',
					'font-style'      => 'normal',
					'line-height'     => '1.417em',
					'text-decoration' => 'none',
					'text-transform'  => 'uppercase',
					'letter-spacing'  => '1px',
					'margin-top'      => '1.3em',
					'margin-bottom'   => '0.84em',
				),
				'h6'      => array(
					'title'           => esc_html__( 'Heading 6', 'jacqueline' ),
					'description'     => sprintf( $font_description, esc_html__( 'tag H6', 'jacqueline' ) ),
					'font-family'     => 'Raleway,sans-serif',
					'font-size'       => '1.188em',
					'font-weight'     => '400',
					'font-style'      => 'normal',
					'line-height'     => '1.474em',
					'text-decoration' => 'none',
					'text-transform'  => 'uppercase',
					'letter-spacing'  => '0.95px',
					'margin-top'      => '1.75em',
					'margin-bottom'   => '1.1em',
				),
				'logo'    => array(
					'title'           => esc_html__( 'Logo text', 'jacqueline' ),
					'description'     => sprintf( $font_description, esc_html__( 'text of the logo', 'jacqueline' ) ),
					'font-family'     => 'Raleway,sans-serif',
					'font-size'       => '1.5em',
					'font-weight'     => '400',
					'font-style'      => 'normal',
					'line-height'     => '1.25em',
					'text-decoration' => 'none',
					'text-transform'  => 'uppercase',
					'letter-spacing'  => '1px',
				),
				'button'  => array(
					'title'           => esc_html__( 'Buttons', 'jacqueline' ),
					'description'     => sprintf( $font_description, esc_html__( 'buttons', 'jacqueline' ) ),
					'font-family'     => 'Raleway,sans-serif',
					'font-size'       => '11px',
					'font-weight'     => '700',
					'font-style'      => 'normal',
					'line-height'     => '21px',
					'text-decoration' => 'none',
					'text-transform'  => 'uppercase',
					'letter-spacing'  => '2.2px',
				),
				'input'   => array(
					'title'           => esc_html__( 'Input fields', 'jacqueline' ),
					'description'     => sprintf( $font_description, esc_html__( 'input fields, dropdowns and textareas', 'jacqueline' ) ),
					'font-family'     => 'inherit',
					'font-size'       => '13px',
					'font-weight'     => '400',
					'font-style'      => 'italic',
					'line-height'     => '1.5em',     // Attention! Firefox don't allow line-height less then 1.5em in the select
					'text-decoration' => 'none',
					'text-transform'  => 'none',
					'letter-spacing'  => '0.3px',
				),
				'info'    => array(
					'title'           => esc_html__( 'Post meta', 'jacqueline' ),
					'description'     => sprintf( $font_description, esc_html__( 'post meta (author, categories, publish date, counters, share, etc.)', 'jacqueline' ) ),
					'font-family'     => 'inherit',
					'font-size'       => '13px',  // Old value '13px' don't allow using 'font zoom' in the custom blog items
					'font-weight'     => '400',
					'font-style'      => 'italic',
					'line-height'     => '1.5em',
					'text-decoration' => 'none',
					'text-transform'  => 'none',
					'letter-spacing'  => '0px',
					'margin-top'      => '0.4em',
					'margin-bottom'   => '',
				),
				'menu'    => array(
					'title'           => esc_html__( 'Main menu', 'jacqueline' ),
					'description'     => sprintf( $font_description, esc_html__( 'main menu items', 'jacqueline' ) ),
					'font-family'     => 'Raleway,sans-serif',
					'font-size'       => '13px',
					'font-weight'     => '600',
					'font-style'      => 'normal',
					'line-height'     => '1.5em',
					'text-decoration' => 'none',
					'text-transform'  => 'uppercase',
					'letter-spacing'  => '0.3px',
				),
				'submenu' => array(
					'title'           => esc_html__( 'Dropdown menu', 'jacqueline' ),
					'description'     => sprintf( $font_description, esc_html__( 'dropdown menu items', 'jacqueline' ) ),
					'font-family'     => 'Raleway,sans-serif',
					'font-size'       => '13px',
					'font-weight'     => '600',
					'font-style'      => 'normal',
					'line-height'     => '1.5em',
					'text-decoration' => 'none',
					'text-transform'  => 'uppercase',
					'letter-spacing'  => '0.3px',
				),
                'decoration' => array(
                    'title'           => esc_html__( 'Decoration', 'jacqueline' ),
                    'description'     => sprintf( $font_description, esc_html__( 'decoration elements', 'jacqueline' ) ),
                    'font-family'     => '"Mr De Haviland",cursive',
                ),
			)
		);

		// Font presets
		jacqueline_storage_set(
			'font_presets', array(
				'karla' => array(
								'title'  => esc_html__( 'Karla', 'jacqueline' ),
								'load_fonts' => array(
													// Google font
													array(
														'name'   => 'Dancing Script',
														'family' => 'fantasy',
														'link'   => '',
														'styles' => '300,400,700',
													),
													// Google font
													array(
														'name'   => 'Sansita Swashed',
														'family' => 'fantasy',
														'link'   => '',
														'styles' => '300,400,700',
													),
												),
								'theme_fonts' => array(
													'p'       => array(
														'font-family'     => '"Dancing Script",fantasy',
														'font-size'       => '1.25rem',
													),
													'post'    => array(
														'font-family'     => '',
													),
													'h1'      => array(
														'font-family'     => '"Sansita Swashed",fantasy',
														'font-size'       => '4em',
													),
													'h2'      => array(
														'font-family'     => '"Sansita Swashed",fantasy',
													),
													'h3'      => array(
														'font-family'     => '"Sansita Swashed",fantasy',
													),
													'h4'      => array(
														'font-family'     => '"Sansita Swashed",fantasy',
													),
													'h5'      => array(
														'font-family'     => '"Sansita Swashed",fantasy',
													),
													'h6'      => array(
														'font-family'     => '"Sansita Swashed",fantasy',
													),
													'logo'    => array(
														'font-family'     => '"Sansita Swashed",fantasy',
													),
													'button'  => array(
														'font-family'     => '"Sansita Swashed",fantasy',
													),
													'input'   => array(
														'font-family'     => 'inherit',
													),
													'info'    => array(
														'font-family'     => 'inherit',
													),
													'menu'    => array(
														'font-family'     => '"Sansita Swashed",fantasy',
													),
													'submenu' => array(
														'font-family'     => '"Sansita Swashed",fantasy',
													),
												),
							),
				'roboto' => array(
								'title'  => esc_html__( 'Roboto', 'jacqueline' ),
								'load_fonts' => array(
													// Google font
													array(
														'name'   => 'Noto Sans JP',
														'family' => 'serif',
														'link'   => '',
														'styles' => '300,300italic,400,400italic,700,700italic',
													),
													// Google font
													array(
														'name'   => 'Merriweather',
														'family' => 'sans-serif',
														'link'   => '',
														'styles' => '300,300italic,400,400italic,700,700italic',
													),
												),
								'theme_fonts' => array(
													'p'       => array(
														'font-family'     => '"Noto Sans JP",serif',
													),
													'post'    => array(
														'font-family'     => '',
													),
													'h1'      => array(
														'font-family'     => 'Merriweather,sans-serif',
													),
													'h2'      => array(
														'font-family'     => 'Merriweather,sans-serif',
													),
													'h3'      => array(
														'font-family'     => 'Merriweather,sans-serif',
													),
													'h4'      => array(
														'font-family'     => 'Merriweather,sans-serif',
													),
													'h5'      => array(
														'font-family'     => 'Merriweather,sans-serif',
													),
													'h6'      => array(
														'font-family'     => 'Merriweather,sans-serif',
													),
													'logo'    => array(
														'font-family'     => 'Merriweather,sans-serif',
													),
													'button'  => array(
														'font-family'     => 'Merriweather,sans-serif',
													),
													'input'   => array(
														'font-family'     => 'inherit',
													),
													'info'    => array(
														'font-family'     => 'inherit',
													),
													'menu'    => array(
														'font-family'     => 'Merriweather,sans-serif',
													),
													'submenu' => array(
														'font-family'     => 'Merriweather,sans-serif',
													),
												),
							),
				'garamond' => array(
								'title'  => esc_html__( 'Garamond', 'jacqueline' ),
								'load_fonts' => array(
													// Adobe font
													array(
														'name'   => 'Europe',
														'family' => 'sans-serif',
														'link'   => 'https://use.typekit.net/qmj1tmx.css',
														'styles' => '',
													),
													// Adobe font
													array(
														'name'   => 'Sofia Pro',
														'family' => 'sans-serif',
														'link'   => 'https://use.typekit.net/qmj1tmx.css',
														'styles' => '',
													),
												),
								'theme_fonts' => array(
													'p'       => array(
														'font-family'     => '"Sofia Pro",sans-serif',
													),
													'post'    => array(
														'font-family'     => '',
													),
													'h1'      => array(
														'font-family'     => 'Europe,sans-serif',
													),
													'h2'      => array(
														'font-family'     => 'Europe,sans-serif',
													),
													'h3'      => array(
														'font-family'     => 'Europe,sans-serif',
													),
													'h4'      => array(
														'font-family'     => 'Europe,sans-serif',
													),
													'h5'      => array(
														'font-family'     => 'Europe,sans-serif',
													),
													'h6'      => array(
														'font-family'     => 'Europe,sans-serif',
													),
													'logo'    => array(
														'font-family'     => 'Europe,sans-serif',
													),
													'button'  => array(
														'font-family'     => 'Europe,sans-serif',
													),
													'input'   => array(
														'font-family'     => 'inherit',
													),
													'info'    => array(
														'font-family'     => 'inherit',
													),
													'menu'    => array(
														'font-family'     => 'Europe,sans-serif',
													),
													'submenu' => array(
														'font-family'     => 'Europe,sans-serif',
													),
												),
							),
			)
		);
	}
}


//--------------------------------------------
// COLOR SCHEMES
//--------------------------------------------
if ( ! function_exists( 'jacqueline_skin_setup_schemes' ) ) {
	add_action( 'after_setup_theme', 'jacqueline_skin_setup_schemes', 1 );
	function jacqueline_skin_setup_schemes() {

		// Theme colors for customizer
		// Attention! Inner scheme must be last in the array below
		jacqueline_storage_set(
			'scheme_color_groups', array(
				'main'    => array(
					'title'       => esc_html__( 'Main', 'jacqueline' ),
					'description' => esc_html__( 'Colors of the main content area', 'jacqueline' ),
				),
				'alter'   => array(
					'title'       => esc_html__( 'Alter', 'jacqueline' ),
					'description' => esc_html__( 'Colors of the alternative blocks (sidebars, etc.)', 'jacqueline' ),
				),
				'extra'   => array(
					'title'       => esc_html__( 'Extra', 'jacqueline' ),
					'description' => esc_html__( 'Colors of the extra blocks (dropdowns, price blocks, table headers, etc.)', 'jacqueline' ),
				),
				'inverse' => array(
					'title'       => esc_html__( 'Inverse', 'jacqueline' ),
					'description' => esc_html__( 'Colors of the inverse blocks - when link color used as background of the block (dropdowns, blockquotes, etc.)', 'jacqueline' ),
				),
				'input'   => array(
					'title'       => esc_html__( 'Input', 'jacqueline' ),
					'description' => esc_html__( 'Colors of the form fields (text field, textarea, select, etc.)', 'jacqueline' ),
				),
			)
		);

		jacqueline_storage_set(
			'scheme_color_names', array(
				'bg_color'    => array(
					'title'       => esc_html__( 'Background color', 'jacqueline' ),
					'description' => esc_html__( 'Background color of this block in the normal state', 'jacqueline' ),
				),
				'bg_hover'    => array(
					'title'       => esc_html__( 'Background hover', 'jacqueline' ),
					'description' => esc_html__( 'Background color of this block in the hovered state', 'jacqueline' ),
				),
				'bd_color'    => array(
					'title'       => esc_html__( 'Border color', 'jacqueline' ),
					'description' => esc_html__( 'Border color of this block in the normal state', 'jacqueline' ),
				),
				'bd_hover'    => array(
					'title'       => esc_html__( 'Border hover', 'jacqueline' ),
					'description' => esc_html__( 'Border color of this block in the hovered state', 'jacqueline' ),
				),
				'text'        => array(
					'title'       => esc_html__( 'Text', 'jacqueline' ),
					'description' => esc_html__( 'Color of the text inside this block', 'jacqueline' ),
				),
				'text_dark'   => array(
					'title'       => esc_html__( 'Text dark', 'jacqueline' ),
					'description' => esc_html__( 'Color of the dark text (bold, header, etc.) inside this block', 'jacqueline' ),
				),
				'text_light'  => array(
					'title'       => esc_html__( 'Text light', 'jacqueline' ),
					'description' => esc_html__( 'Color of the light text (post meta, etc.) inside this block', 'jacqueline' ),
				),
				'text_link'   => array(
					'title'       => esc_html__( 'Link', 'jacqueline' ),
					'description' => esc_html__( 'Color of the links inside this block', 'jacqueline' ),
				),
				'text_hover'  => array(
					'title'       => esc_html__( 'Link hover', 'jacqueline' ),
					'description' => esc_html__( 'Color of the hovered state of links inside this block', 'jacqueline' ),
				),
				'text_link2'  => array(
					'title'       => esc_html__( 'Accent 2', 'jacqueline' ),
					'description' => esc_html__( 'Color of the accented texts (areas) inside this block', 'jacqueline' ),
				),
				'text_hover2' => array(
					'title'       => esc_html__( 'Accent 2 hover', 'jacqueline' ),
					'description' => esc_html__( 'Color of the hovered state of accented texts (areas) inside this block', 'jacqueline' ),
				),
				'text_link3'  => array(
					'title'       => esc_html__( 'Accent 3', 'jacqueline' ),
					'description' => esc_html__( 'Color of the other accented texts (buttons) inside this block', 'jacqueline' ),
				),
				'text_hover3' => array(
					'title'       => esc_html__( 'Accent 3 hover', 'jacqueline' ),
					'description' => esc_html__( 'Color of the hovered state of other accented texts (buttons) inside this block', 'jacqueline' ),
				),
			)
		);

		// Default values for each color scheme
		$schemes = array(

			// Color scheme: 'default'
			'default' => array(
				'title'    => esc_html__( 'Default', 'jacqueline' ),
				'internal' => true,
				'colors'   => array(

					// Whole block border and background
					'bg_color'         => '#F8F8F8', //
					'bd_color'         => '#EAEAEA', //

					// Text and links colors
					'text'             => '#757575', //
					'text_light'       => '#9A9A9A', //
					'text_dark'        => '#323232', //
					'text_link'        => '#F9A392', //
					'text_hover'       => '#8ED4CC', //
					'text_link2'       => '#8ED4CC', //
					'text_hover2'      => '#F9A392', //
					'text_link3'       => '#F9A392', //
					'text_hover3'      => '#F09988', //

					// Alternative blocks (sidebar, tabs, alternative blocks, etc.)
					'alter_bg_color'   => '#FFFFFF', //
					'alter_bg_hover'   => '#F9F9F9', //
					'alter_bd_color'   => '#F2F2F2', //
					'alter_bd_hover'   => '#E2E2E2', //
					'alter_text'       => '#757575', //
					'alter_light'      => '#9A9A9A', //
					'alter_dark'       => '#323232', //
					'alter_link'       => '#F9A392', //
					'alter_hover'      => '#8ED4CC', //
					'alter_link2'      => '#8ED4CC', //
					'alter_hover2'     => '#F9A392', //
					'alter_link3'      => '#F9A392', //
					'alter_hover3'     => '#F09988', //

					// Extra blocks (submenu, tabs, color blocks, etc.)
					'extra_bg_color'   => '#FFFFFF', //
					'extra_bg_hover'   => '#3f3d47',
					'extra_bd_color'   => '#F3F3F3', //
					'extra_bd_hover'   => '#575757',
					'extra_text'       => '#757575', //
					'extra_light'      => '#afafaf',
					'extra_dark'       => '#232A34', //
					'extra_link'       => '#8ED4CC', //
					'extra_hover'      => '#F9A392', //
					'extra_link2'      => '#80d572',
					'extra_hover2'     => '#8be77c',
					'extra_link3'      => '#ddb837',
					'extra_hover3'     => '#eec432',

					// Input fields (form's fields and textarea)
					'input_bg_color'   => 'transparent', //
					'input_bg_hover'   => 'transparent', //
					'input_bd_color'   => '#EAEAEA', //
					'input_bd_hover'   => '#F9A392', //
					'input_text'       => '#757575', //
					'input_light'      => '#9A9A9A', //
					'input_dark'       => '#323232', //

					// Inverse blocks (text and links on the 'text_link' background)
					'inverse_bd_color' => '#67bcc1',
					'inverse_bd_hover' => '#5aa4a9',
					'inverse_text'     => '#1d1d1d',
					'inverse_light'    => '#333333',
					'inverse_dark'     => '#323232', //
					'inverse_link'     => '#FFFFFF', //
					'inverse_hover'    => '#FFFFFF', //

					// Additional (skin-specific) colors.
					// Attention! Set of colors must be equal in all color schemes.
					//---> For example:
					//---> 'new_color1'         => '#rrggbb',
					//---> 'alter_new_color1'   => '#rrggbb',
					//---> 'inverse_new_color1' => '#rrggbb',
				),
			),

			// Color scheme: 'dark'
			'dark'    => array(
				'title'    => esc_html__( 'Dark', 'jacqueline' ),
				'internal' => true,
				'colors'   => array(

					// Whole block border and background
					'bg_color'         => '#1C1C1C', //
					'bd_color'         => '#323232', //

					// Text and links colors
					'text'             => '#9A9A9A', //
					'text_light'       => '#848484', //
					'text_dark'        => '#FFFFFF', //
					'text_link'        => '#F9A392', //
					'text_hover'       => '#8ED4CC', //
					'text_link2'       => '#8ED4CC', //
					'text_hover2'      => '#F9A392', //
					'text_link3'       => '#F9A392', //
					'text_hover3'      => '#F09988', //

					// Alternative blocks (sidebar, tabs, alternative blocks, etc.)
					'alter_bg_color'   => '#323232', //
					'alter_bg_hover'   => '#262626', //
					'alter_bd_color'   => '#434343', //
					'alter_bd_hover'   => '#5D5D5D', //
					'alter_text'       => '#9A9A9A', //
					'alter_light'      => '#848484', //
					'alter_dark'       => '#FFFFFF', //
					'alter_link'       => '#F9A392', //
					'alter_hover'      => '#8ED4CC', //
					'alter_link2'      => '#8ED4CC', //
					'alter_hover2'     => '#F9A392', //
					'alter_link3'      => '#F9A392', //
					'alter_hover3'     => '#F09988', //

					// Extra blocks (submenu, tabs, color blocks, etc.)
					'extra_bg_color'   => '#212121', //
					'extra_bg_hover'   => '#3f3d47',
					'extra_bd_color'   => '#424242', //
					'extra_bd_hover'   => '#575757',
					'extra_text'       => '#9A9A9A', //
					'extra_light'      => '#afafaf',
					'extra_dark'       => '#FFFFFF', //
					'extra_link'       => '#8ED4CC', //
					'extra_hover'      => '#F9A392', //
					'extra_link2'      => '#80d572',
					'extra_hover2'     => '#8be77c',
					'extra_link3'      => '#ddb837',
					'extra_hover3'     => '#eec432',

					// Input fields (form's fields and textarea)
					'input_bg_color'   => '#transparent', //
					'input_bg_hover'   => '#transparent', //
					'input_bd_color'   => '#434343', //
					'input_bd_hover'   => '#F9A392', //
					'input_text'       => '#9A9A9A', //
					'input_light'      => '#848484', //
					'input_dark'       => '#FFFFFF', //

					// Inverse blocks (text and links on the 'text_link' background)
					'inverse_bd_color' => '#e36650',
					'inverse_bd_hover' => '#cb5b47',
					'inverse_text'     => '#FFFFFF', //
					'inverse_light'    => '#6f6f6f',
					'inverse_dark'     => '#323232', //
					'inverse_link'     => '#FFFFFF', //
					'inverse_hover'    => '#323232', //

					// Additional (skin-specific) colors.
					// Attention! Set of colors must be equal in all color schemes.
					//---> For example:
					//---> 'new_color1'         => '#rrggbb',
					//---> 'alter_new_color1'   => '#rrggbb',
					//---> 'inverse_new_color1' => '#rrggbb',
				),
			),

            // Color scheme: 'light'
            'light' => array(
                'title'    => esc_html__( 'Light', 'jacqueline' ),
                'internal' => true,
                'colors'   => array(

                    // Whole block border and background
                    'bg_color'         => '#FFFFFF', //
                    'bd_color'         => '#EAEAEA', //

                    // Text and links colors
                    'text'             => '#757575', //
                    'text_light'       => '#9A9A9A', //
                    'text_dark'        => '#323232', //
                    'text_link'        => '#F9A392', //
                    'text_hover'       => '#8ED4CC', //
                    'text_link2'       => '#8ED4CC', //
                    'text_hover2'      => '#F9A392', //
                    'text_link3'       => '#F9A392', //
                    'text_hover3'      => '#F09988', //

                    // Alternative blocks (sidebar, tabs, alternative blocks, etc.)
                    'alter_bg_color'   => '#F8F8F8', //
                    'alter_bg_hover'   => '#FFFFFF', //
                    'alter_bd_color'   => '#EAEAEA', //
                    'alter_bd_hover'   => '#E2E2E2', //
                    'alter_text'       => '#757575', //
                    'alter_light'      => '#9A9A9A', //
                    'alter_dark'       => '#323232', //
                    'alter_link'       => '#F9A392', //
                    'alter_hover'      => '#8ED4CC', //
                    'alter_link2'      => '#8ED4CC', //
                    'alter_hover2'     => '#F9A392', //
                    'alter_link3'      => '#F9A392', //
                    'alter_hover3'     => '#F09988', //

                    // Extra blocks (submenu, tabs, color blocks, etc.)
                    'extra_bg_color'   => '#F3F3F3', //
                    'extra_bg_hover'   => '#3f3d47',
                    'extra_bd_color'   => '#FFFFFF', //
                    'extra_bd_hover'   => '#575757',
                    'extra_text'       => '#757575', //
                    'extra_light'      => '#afafaf',
                    'extra_dark'       => '#232A34', //
                    'extra_link'       => '#8ED4CC', //
                    'extra_hover'      => '#F9A392', //
                    'extra_link2'      => '#80d572',
                    'extra_hover2'     => '#8be77c',
                    'extra_link3'      => '#ddb837',
                    'extra_hover3'     => '#eec432',

                    // Input fields (form's fields and textarea)
                    'input_bg_color'   => 'transparent', //
                    'input_bg_hover'   => 'transparent', //
                    'input_bd_color'   => '#EAEAEA', //
                    'input_bd_hover'   => '#F9A392', //
                    'input_text'       => '#757575', //
                    'input_light'      => '#9A9A9A', //
                    'input_dark'       => '#323232', //

                    // Inverse blocks (text and links on the 'text_link' background)
                    'inverse_bd_color' => '#67bcc1',
                    'inverse_bd_hover' => '#5aa4a9',
                    'inverse_text'     => '#1d1d1d',
                    'inverse_light'    => '#333333',
                    'inverse_dark'     => '#323232', //
                    'inverse_link'     => '#FFFFFF', //
                    'inverse_hover'    => '#FFFFFF', //

                    // Additional (skin-specific) colors.
                    // Attention! Set of colors must be equal in all color schemes.
                    //---> For example:
                    //---> 'new_color1'         => '#rrggbb',
                    //---> 'alter_new_color1'   => '#rrggbb',
                    //---> 'inverse_new_color1' => '#rrggbb',
                ),
            ),

            // Color scheme: 'violet'
            'violet' => array(
                'title'    => esc_html__( 'Violet', 'jacqueline' ),
                'internal' => true,
                'colors'   => array(

                    // Whole block border and background
                    'bg_color'         => '#FFFFFF', //
                    'bd_color'         => '#EAEAEA', //

                    // Text and links colors
                    'text'             => '#757575', //
                    'text_light'       => '#9A9A9A', //
                    'text_dark'        => '#323232', //
                    'text_link'        => '#9988D0', //
                    'text_hover'       => '#FF9F7C', //
                    'text_link2'       => '#FF9F7C', //
                    'text_hover2'      => '#9988D0', //
                    'text_link3'       => '#9988D0', //
                    'text_hover3'      => '#A998E1', //

                    // Alternative blocks (sidebar, tabs, alternative blocks, etc.)
                    'alter_bg_color'   => '#EFF0FB', //
                    'alter_bg_hover'   => '#FFFFFF', //
                    'alter_bd_color'   => '#DFE4EC', //
                    'alter_bd_hover'   => '#D4D8DD', //
                    'alter_text'       => '#757575', //
                    'alter_light'      => '#9A9A9A', //
                    'alter_dark'       => '#323232', //
                    'alter_link'       => '#9988D0', //
                    'alter_hover'      => '#FF9F7C', //
                    'alter_link2'      => '#FF9F7C', //
                    'alter_hover2'     => '#9988D0', //
                    'alter_link3'      => '#9988D0', //
                    'alter_hover3'     => '#A998E1', //

                    // Extra blocks (submenu, tabs, color blocks, etc.)
                    'extra_bg_color'   => '#FFFFFF', //
                    'extra_bg_hover'   => '#3f3d47',
                    'extra_bd_color'   => '#F3F3F3', //
                    'extra_bd_hover'   => '#575757',
                    'extra_text'       => '#757575', //
                    'extra_light'      => '#afafaf',
                    'extra_dark'       => '#232A34', //
                    'extra_link'       => '#FF9F7C', //
                    'extra_hover'      => '#A998E1', //
                    'extra_link2'      => '#80d572',
                    'extra_hover2'     => '#8be77c',
                    'extra_link3'      => '#ddb837',
                    'extra_hover3'     => '#eec432',

                    // Input fields (form's fields and textarea)
                    'input_bg_color'   => 'transparent', //
                    'input_bg_hover'   => 'transparent', //
                    'input_bd_color'   => '#DFE4EC', //
                    'input_bd_hover'   => '#9988D0', //
                    'input_text'       => '#757575', //
                    'input_light'      => '#9A9A9A', //
                    'input_dark'       => '#323232', //

                    // Inverse blocks (text and links on the 'text_link' background)
                    'inverse_bd_color' => '#67bcc1',
                    'inverse_bd_hover' => '#5aa4a9',
                    'inverse_text'     => '#1d1d1d',
                    'inverse_light'    => '#333333',
                    'inverse_dark'     => '#323232', //
                    'inverse_link'     => '#FFFFFF', //
                    'inverse_hover'    => '#FFFFFF', //

                    // Additional (skin-specific) colors.
                    // Attention! Set of colors must be equal in all color schemes.
                    //---> For example:
                    //---> 'new_color1'         => '#rrggbb',
                    //---> 'alter_new_color1'   => '#rrggbb',
                    //---> 'inverse_new_color1' => '#rrggbb',
                ),
            ),

            // Color scheme: 'violet_dark'
            'violet_dark'    => array(
                'title'    => esc_html__( 'Violet Dark', 'jacqueline' ),
                'internal' => true,
                'colors'   => array(

                    // Whole block border and background
                    'bg_color'         => '#1C1C1C', //
                    'bd_color'         => '#323232', //

                    // Text and links colors
                    'text'             => '#9A9A9A', //
                    'text_light'       => '#848484', //
                    'text_dark'        => '#FFFFFF', //
                    'text_link'        => '#9988D0', //
                    'text_hover'       => '#FF9F7C', //
                    'text_link2'       => '#FF9F7C', //
                    'text_hover2'      => '#9988D0', //
                    'text_link3'       => '#9988D0', //
                    'text_hover3'      => '#A998E1', //

                    // Alternative blocks (sidebar, tabs, alternative blocks, etc.)
                    'alter_bg_color'   => '#323232', //
                    'alter_bg_hover'   => '#262626', //
                    'alter_bd_color'   => '#434343', //
                    'alter_bd_hover'   => '#5D5D5D', //
                    'alter_text'       => '#9A9A9A', //
                    'alter_light'      => '#848484', //
                    'alter_dark'       => '#FFFFFF', //
                    'alter_link'       => '#9988D0', //
                    'alter_hover'      => '#FF9F7C', //
                    'alter_link2'      => '#FF9F7C', //
                    'alter_hover2'     => '#9988D0', //
                    'alter_link3'      => '#9988D0', //
                    'alter_hover3'     => '#A998E1', //

                    // Extra blocks (submenu, tabs, color blocks, etc.)
                    'extra_bg_color'   => '#212121', //
                    'extra_bg_hover'   => '#3f3d47',
                    'extra_bd_color'   => '#424242', //
                    'extra_bd_hover'   => '#575757',
                    'extra_text'       => '#9A9A9A', //
                    'extra_light'      => '#afafaf',
                    'extra_dark'       => '#FFFFFF', //
                    'extra_link'       => '#FF9F7C', //
                    'extra_hover'      => '#9988D0', //
                    'extra_link2'      => '#80d572',
                    'extra_hover2'     => '#8be77c',
                    'extra_link3'      => '#ddb837',
                    'extra_hover3'     => '#eec432',

                    // Input fields (form's fields and textarea)
                    'input_bg_color'   => '#transparent', //
                    'input_bg_hover'   => '#transparent', //
                    'input_bd_color'   => '#434343', //
                    'input_bd_hover'   => '#9988D0', //
                    'input_text'       => '#9A9A9A', //
                    'input_light'      => '#848484', //
                    'input_dark'       => '#FFFFFF', //

                    // Inverse blocks (text and links on the 'text_link' background)
                    'inverse_bd_color' => '#e36650',
                    'inverse_bd_hover' => '#cb5b47',
                    'inverse_text'     => '#FFFFFF', //
                    'inverse_light'    => '#6f6f6f',
                    'inverse_dark'     => '#323232', //
                    'inverse_link'     => '#FFFFFF', //
                    'inverse_hover'    => '#323232', //

                    // Additional (skin-specific) colors.
                    // Attention! Set of colors must be equal in all color schemes.
                    //---> For example:
                    //---> 'new_color1'         => '#rrggbb',
                    //---> 'alter_new_color1'   => '#rrggbb',
                    //---> 'inverse_new_color1' => '#rrggbb',
                ),
            ),

            // Color scheme: 'greeny'
            'greeny' => array(
                'title'    => esc_html__( 'Greeny', 'jacqueline' ),
                'internal' => true,
                'colors'   => array(

                    // Whole block border and background
                    'bg_color'         => '#FFFFFF', //
                    'bd_color'         => '#ECE7E3', //

                    // Text and links colors
                    'text'             => '#757575', //
                    'text_light'       => '#9A9A9A', //
                    'text_dark'        => '#323232', //
                    'text_link'        => '#486830', //
                    'text_hover'       => '#E0AA83', //
                    'text_link2'       => '#E0AA83', //
                    'text_hover2'      => '#486830', //
                    'text_link3'       => '#486830', //
                    'text_hover3'      => '#567B3A', //

                    // Alternative blocks (sidebar, tabs, alternative blocks, etc.)
                    'alter_bg_color'   => '#F8F5F1', //
                    'alter_bg_hover'   => '#FFFFFF', //
                    'alter_bd_color'   => '#ECE7E3', //
                    'alter_bd_hover'   => '#DFDCDA', //
                    'alter_text'       => '#757575', //
                    'alter_light'      => '#9A9A9A', //
                    'alter_dark'       => '#323232', //
                    'alter_link'       => '#486830', //
                    'alter_hover'      => '#E0AA83', //
                    'alter_link2'      => '#E0AA83', //
                    'alter_hover2'     => '#486830', //
                    'alter_link3'      => '#486830', //
                    'alter_hover3'     => '#567B3A', //

                    // Extra blocks (submenu, tabs, color blocks, etc.)
                    'extra_bg_color'   => '#FFFFFF', //
                    'extra_bg_hover'   => '#3f3d47',
                    'extra_bd_color'   => '#F3F3F3', //
                    'extra_bd_hover'   => '#575757',
                    'extra_text'       => '#757575', //
                    'extra_light'      => '#afafaf',
                    'extra_dark'       => '#232A34', //
                    'extra_link'       => '#E0AA83', //
                    'extra_hover'      => '#486830', //
                    'extra_link2'      => '#80d572',
                    'extra_hover2'     => '#8be77c',
                    'extra_link3'      => '#ddb837',
                    'extra_hover3'     => '#eec432',

                    // Input fields (form's fields and textarea)
                    'input_bg_color'   => 'transparent', //
                    'input_bg_hover'   => 'transparent', //
                    'input_bd_color'   => '#DFDCDA', //
                    'input_bd_hover'   => '#486830', //
                    'input_text'       => '#757575', //
                    'input_light'      => '#9A9A9A', //
                    'input_dark'       => '#323232', //

                    // Inverse blocks (text and links on the 'text_link' background)
                    'inverse_bd_color' => '#67bcc1',
                    'inverse_bd_hover' => '#5aa4a9',
                    'inverse_text'     => '#1d1d1d',
                    'inverse_light'    => '#333333',
                    'inverse_dark'     => '#323232', //
                    'inverse_link'     => '#FFFFFF', //
                    'inverse_hover'    => '#FFFFFF', //

                    // Additional (skin-specific) colors.
                    // Attention! Set of colors must be equal in all color schemes.
                    //---> For example:
                    //---> 'new_color1'         => '#rrggbb',
                    //---> 'alter_new_color1'   => '#rrggbb',
                    //---> 'inverse_new_color1' => '#rrggbb',
                ),
            ),

            // Color scheme: 'greeny_dark'
            'greeny_dark'    => array(
                'title'    => esc_html__( 'Greeny Dark', 'jacqueline' ),
                'internal' => true,
                'colors'   => array(

                    // Whole block border and background
                    'bg_color'         => '#1C1C1C', //
                    'bd_color'         => '#323232', //

                    // Text and links colors
                    'text'             => '#9A9A9A', //
                    'text_light'       => '#848484', //
                    'text_dark'        => '#FFFFFF', //
                    'text_link'        => '#486830', //
                    'text_hover'       => '#E0AA83', //
                    'text_link2'       => '#E0AA83', //
                    'text_hover2'      => '#486830', //
                    'text_link3'       => '#486830', //
                    'text_hover3'      => '#567B3A', //

                    // Alternative blocks (sidebar, tabs, alternative blocks, etc.)
                    'alter_bg_color'   => '#323232', //
                    'alter_bg_hover'   => '#262626', //
                    'alter_bd_color'   => '#434343', //
                    'alter_bd_hover'   => '#5D5D5D', //
                    'alter_text'       => '#9A9A9A', //
                    'alter_light'      => '#848484', //
                    'alter_dark'       => '#FFFFFF', //
                    'alter_link'       => '#486830', //
                    'alter_hover'      => '#E0AA83', //
                    'alter_link2'      => '#E0AA83', //
                    'alter_hover2'     => '#486830', //
                    'alter_link3'      => '#486830', //
                    'alter_hover3'     => '#567B3A', //

                    // Extra blocks (submenu, tabs, color blocks, etc.)
                    'extra_bg_color'   => '#212121', //
                    'extra_bg_hover'   => '#3f3d47',
                    'extra_bd_color'   => '#424242', //
                    'extra_bd_hover'   => '#575757',
                    'extra_text'       => '#9A9A9A', //
                    'extra_light'      => '#afafaf',
                    'extra_dark'       => '#FFFFFF', //
                    'extra_link'       => '#486830', //
                    'extra_hover'      => '#E0AA83', //
                    'extra_link2'      => '#80d572',
                    'extra_hover2'     => '#8be77c',
                    'extra_link3'      => '#ddb837',
                    'extra_hover3'     => '#eec432',

                    // Input fields (form's fields and textarea)
                    'input_bg_color'   => '#transparent', //
                    'input_bg_hover'   => '#transparent', //
                    'input_bd_color'   => '#434343', //
                    'input_bd_hover'   => '#486830', //
                    'input_text'       => '#9A9A9A', //
                    'input_light'      => '#848484', //
                    'input_dark'       => '#FFFFFF', //

                    // Inverse blocks (text and links on the 'text_link' background)
                    'inverse_bd_color' => '#e36650',
                    'inverse_bd_hover' => '#cb5b47',
                    'inverse_text'     => '#FFFFFF', //
                    'inverse_light'    => '#6f6f6f',
                    'inverse_dark'     => '#323232', //
                    'inverse_link'     => '#FFFFFF', //
                    'inverse_hover'    => '#323232', //

                    // Additional (skin-specific) colors.
                    // Attention! Set of colors must be equal in all color schemes.
                    //---> For example:
                    //---> 'new_color1'         => '#rrggbb',
                    //---> 'alter_new_color1'   => '#rrggbb',
                    //---> 'inverse_new_color1' => '#rrggbb',
                ),
            ),
			// Color scheme: 'oceanic'
			'oceanic' => array(
				'title'    => esc_html__( 'Oceanic', 'jacqueline' ),
				'internal' => true,
				'colors'   => array(

					// Whole block border and background
					'bg_color'         => '#FFFFFF', //
					'bd_color'         => '#ECF1F4', //

					// Text and links colors
					'text'             => '#5A5A67', //
					'text_light'       => '#898A8E', //
					'text_dark'        => '#191F29', //
					'text_link'        => '#70CAD1', //
					'text_hover'       => '#38B1BA', //
					'text_link2'       => '#BBB7EA', //
					'text_hover2'      => '#918BD6', //
					'text_link3'       => '#618B4A', //
					'text_hover3'      => '#3D6926', //

					// Alternative blocks (sidebar, tabs, alternative blocks, etc.)
					'alter_bg_color'   => '#F4F8FA', //
					'alter_bg_hover'   => '#E5EDF2', //
					'alter_bd_color'   => '#CFD9DE', //
					'alter_bd_hover'   => '#C6D3D9', //
					'alter_text'       => '#5A5A67', //
					'alter_light'      => '#898A8E', //
					'alter_dark'       => '#191F29', //
					'alter_link'       => '#70CAD1', //
					'alter_hover'      => '#38B1BA', //
					'alter_link2'      => '#BBB7EA', //
					'alter_hover2'     => '#918BD6', //
					'alter_link3'      => '#618B4A', //
					'alter_hover3'     => '#3D6926', //

					// Extra blocks (submenu, tabs, color blocks, etc.)
					'extra_bg_color'   => '#FFFFFF', //
					'extra_bg_hover'   => '#3f3d47',
					'extra_bd_color'   => '#F3F3F3', //
					'extra_bd_hover'   => '#575757',
					'extra_text'       => '#757575', //
					'extra_light'      => '#afafaf',
					'extra_dark'       => '#232A34', //
					'extra_link'       => '#70CAD1', //
					'extra_hover'      => '#38B1BA', //
					'extra_link2'      => '#80d572',
					'extra_hover2'     => '#8be77c',
					'extra_link3'      => '#ddb837',
					'extra_hover3'     => '#eec432',

					// Input fields (form's fields and textarea)
					'input_bg_color'   => 'transparent', //
					'input_bg_hover'   => 'transparent', //
					'input_bd_color'   => '#CFD9DE', //
					'input_bd_hover'   => '#C6D3D9', //
					'input_text'       => '#5A5A67', //
					'input_light'      => '#898A8E', //
					'input_dark'       => '#191F29', //

					// Inverse blocks (text and links on the 'text_link' background)
					'inverse_bd_color' => '#67bcc1',
					'inverse_bd_hover' => '#5aa4a9',
					'inverse_text'     => '#1d1d1d',
					'inverse_light'    => '#333333',
					'inverse_dark'     => '#191F29', //
					'inverse_link'     => '#FFFFFF', //
					'inverse_hover'    => '#FFFFFF', //

					// Additional (skin-specific) colors.
					// Attention! Set of colors must be equal in all color schemes.
					//---> For example:
					//---> 'new_color1'         => '#rrggbb',
					//---> 'alter_new_color1'   => '#rrggbb',
					//---> 'inverse_new_color1' => '#rrggbb',
				),
			),

			// Color scheme: 'oceanic_dark'
			'oceanic_dark'    => array(
				'title'    => esc_html__( 'Oceanic Dark', 'jacqueline' ),
				'internal' => true,
				'colors'   => array(

					// Whole block border and background
					'bg_color'         => '#010813', //
					'bd_color'         => '#232B40', //

					// Text and links colors
					'text'             => '#9BA2AD', //
					'text_light'       => '#A3A7AD', //
					'text_dark'        => '#FFFFFF', //
					'text_link'        => '#70CAD1', //
					'text_hover'       => '#38B1BA', //
					'text_link2'       => '#BBB7EA', //
					'text_hover2'      => '#918BD6', //
					'text_link3'       => '#618B4A', //
					'text_hover3'      => '#3D6926', //

					// Alternative blocks (sidebar, tabs, alternative blocks, etc.)
					'alter_bg_color'   => '#0B0F1C', //
					'alter_bg_hover'   => '#191F32', //
					'alter_bd_color'   => '#232B40', //
					'alter_bd_hover'   => '#2F3748', //
					'alter_text'       => '#9BA2AD', //
					'alter_light'      => '#A3A7AD', //
					'alter_dark'       => '#FFFFFF', //
					'alter_link'       => '#70CAD1', //
					'alter_hover'      => '#38B1BA', //
					'alter_link2'      => '#BBB7EA', //
					'alter_hover2'     => '#918BD6', //
					'alter_link3'      => '#618B4A', //
					'alter_hover3'     => '#3D6926', //

					// Extra blocks (submenu, tabs, color blocks, etc.)
					'extra_bg_color'   => '#121422', //
					'extra_bg_hover'   => '#16192D',
					'extra_bd_color'   => '#2A2E39', //
					'extra_bd_hover'   => '#575757',
					'extra_text'       => '#9BA2AD', //
					'extra_light'      => '#afafaf',
					'extra_dark'       => '#FFFFFF', //
					'extra_link'       => '#70CAD1', //
					'extra_hover'      => '#38B1BA', //
					'extra_link2'      => '#80d572',
					'extra_hover2'     => '#8be77c',
					'extra_link3'      => '#ddb837',
					'extra_hover3'     => '#eec432',

					// Input fields (form's fields and textarea)
					'input_bg_color'   => '#transparent', //
					'input_bg_hover'   => '#transparent', //
					'input_bd_color'   => '#232B40', //
					'input_bd_hover'   => '#2F3748', //
					'input_text'       => '#9BA2AD', //
					'input_light'      => '#A3A7AD', //
					'input_dark'       => '#FFFFFF', //

					// Inverse blocks (text and links on the 'text_link' background)
					'inverse_bd_color' => '#e36650',
					'inverse_bd_hover' => '#cb5b47',
					'inverse_text'     => '#FFFFFF', //
					'inverse_light'    => '#6f6f6f',
					'inverse_dark'     => '#191F29', //
					'inverse_link'     => '#FFFFFF', //
					'inverse_hover'    => '#191F29', //

					// Additional (skin-specific) colors.
					// Attention! Set of colors must be equal in all color schemes.
					//---> For example:
					//---> 'new_color1'         => '#rrggbb',
					//---> 'alter_new_color1'   => '#rrggbb',
					//---> 'inverse_new_color1' => '#rrggbb',
				),
			),
		);
		jacqueline_storage_set( 'schemes', $schemes );
		jacqueline_storage_set( 'schemes_original', $schemes );

		// Add names of additional colors
		//---> For example:
		//---> jacqueline_storage_set_array( 'scheme_color_names', 'new_color1', array(
		//---> 	'title'       => __( 'New color 1', 'jacqueline' ),
		//---> 	'description' => __( 'Description of the new color 1', 'jacqueline' ),
		//---> ) );


		// Additional colors for each scheme
		// Parameters:	'color' - name of the color from the scheme that should be used as source for the transformation
		//				'alpha' - to make color transparent (0.0 - 1.0)
		//				'hue', 'saturation', 'brightness' - inc/dec value for each color's component
		jacqueline_storage_set(
			'scheme_colors_add', array(
				'bg_color_0'        => array(
					'color' => 'bg_color',
					'alpha' => 0,
				),
				'bg_color_02'       => array(
					'color' => 'bg_color',
					'alpha' => 0.2,
				),
				'bg_color_07'       => array(
					'color' => 'bg_color',
					'alpha' => 0.7,
				),
				'bg_color_08'       => array(
					'color' => 'bg_color',
					'alpha' => 0.8,
				),
				'bg_color_09'       => array(
					'color' => 'bg_color',
					'alpha' => 0.9,
				),
				'alter_bg_color_07' => array(
					'color' => 'alter_bg_color',
					'alpha' => 0.7,
				),
				'alter_bg_color_04' => array(
					'color' => 'alter_bg_color',
					'alpha' => 0.4,
				),
				'alter_bg_color_00' => array(
					'color' => 'alter_bg_color',
					'alpha' => 0,
				),
				'alter_bg_color_02' => array(
					'color' => 'alter_bg_color',
					'alpha' => 0.2,
				),
				'alter_bd_color_02' => array(
					'color' => 'alter_bd_color',
					'alpha' => 0.2,
				),
                'alter_dark_009'     => array(
                    'color' => 'alter_dark',
                    'alpha' => 0.09,
                ),
                'alter_dark_015'     => array(
                    'color' => 'alter_dark',
                    'alpha' => 0.15,
                ),
                'alter_dark_02'     => array(
                    'color' => 'alter_dark',
                    'alpha' => 0.2,
                ),
                'alter_dark_05'     => array(
                    'color' => 'alter_dark',
                    'alpha' => 0.5,
                ),
                'alter_dark_08'     => array(
                    'color' => 'alter_dark',
                    'alpha' => 0.8,
                ),
				'alter_link_02'     => array(
					'color' => 'alter_link',
					'alpha' => 0.2,
				),
				'alter_link_07'     => array(
					'color' => 'alter_link',
					'alpha' => 0.7,
				),
				'extra_bg_color_05' => array(
					'color' => 'extra_bg_color',
					'alpha' => 0.5,
				),
				'extra_bg_color_07' => array(
					'color' => 'extra_bg_color',
					'alpha' => 0.7,
				),
				'extra_link_02'     => array(
					'color' => 'extra_link',
					'alpha' => 0.2,
				),
				'extra_link_07'     => array(
					'color' => 'extra_link',
					'alpha' => 0.7,
				),
                'text_dark_003'      => array(
                    'color' => 'text_dark',
                    'alpha' => 0.03,
                ),
                'text_dark_005'      => array(
                    'color' => 'text_dark',
                    'alpha' => 0.05,
                ),
                'text_dark_008'      => array(
                    'color' => 'text_dark',
                    'alpha' => 0.08,
                ),
                'text_dark_009'      => array(
                    'color' => 'text_dark',
                    'alpha' => 0.09,
                ),
                'text_dark_01'      => array(
                    'color' => 'text_dark',
                    'alpha' => 0.1,
                ),
				'text_dark_015'      => array(
					'color' => 'text_dark',
					'alpha' => 0.15,
				),
				'text_dark_02'      => array(
					'color' => 'text_dark',
					'alpha' => 0.2,
				),
                'text_dark_03'      => array(
                    'color' => 'text_dark',
                    'alpha' => 0.3,
                ),
                'text_dark_05'      => array(
                    'color' => 'text_dark',
                    'alpha' => 0.5,
                ),
				'text_dark_07'      => array(
					'color' => 'text_dark',
					'alpha' => 0.7,
				),
                'text_dark_08'      => array(
                    'color' => 'text_dark',
                    'alpha' => 0.8,
                ),
                'text_link_007'      => array(
                    'color' => 'text_link',
                    'alpha' => 0.07,
                ),
				'text_link_02'      => array(
					'color' => 'text_link',
					'alpha' => 0.2,
				),
                'text_link_03'      => array(
                    'color' => 'text_link',
                    'alpha' => 0.3,
                ),
				'text_link_04'      => array(
					'color' => 'text_link',
					'alpha' => 0.4,
				),
                'text_link_06'      => array(
                    'color' => 'text_link',
                    'alpha' => 0.6,
                ),
				'text_link_07'      => array(
					'color' => 'text_link',
					'alpha' => 0.7,
				),
                'text_link2_007'      => array(
                    'color' => 'text_link2',
                    'alpha' => 0.07,
                ),
				'text_link2_02'      => array(
					'color' => 'text_link2',
					'alpha' => 0.2,
				),
                'text_link2_03'      => array(
                    'color' => 'text_link2',
                    'alpha' => 0.3,
                ),
				'text_link2_05'      => array(
					'color' => 'text_link2',
					'alpha' => 0.5,
				),
                'text_link3_007'      => array(
                    'color' => 'text_link3',
                    'alpha' => 0.07,
                ),
				'text_link3_02'      => array(
					'color' => 'text_link3',
					'alpha' => 0.2,
				),
                'text_link3_03'      => array(
                    'color' => 'text_link3',
                    'alpha' => 0.3,
                ),
                'inverse_text_03'      => array(
                    'color' => 'inverse_text',
                    'alpha' => 0.3,
                ),
                'inverse_link_08'      => array(
                    'color' => 'inverse_link',
                    'alpha' => 0.8,
                ),
                'inverse_hover_08'      => array(
                    'color' => 'inverse_hover',
                    'alpha' => 0.8,
                ),
				'text_dark_blend'   => array(
					'color'      => 'text_dark',
					'hue'        => 2,
					'saturation' => -5,
					'brightness' => 5,
				),
				'text_link_blend'   => array(
					'color'      => 'text_link',
					'hue'        => 2,
					'saturation' => -5,
					'brightness' => 5,
				),
				'alter_link_blend'  => array(
					'color'      => 'alter_link',
					'hue'        => 2,
					'saturation' => -5,
					'brightness' => 5,
				),
                'alter_link2_blend'  => array(
                    'color'      => 'alter_link2',
                    'hue'        => 2,
                    'saturation' => -5,
                    'brightness' => 5,
                ),
			)
		);

		// Simple scheme editor: lists the colors to edit in the "Simple" mode.
		// For each color you can set the array of 'slave' colors and brightness factors that are used to generate new values,
		// when 'main' color is changed
		// Leave 'slave' arrays empty if your scheme does not have a color dependency
		jacqueline_storage_set(
			'schemes_simple', array(
				'text_link'        => array(),
				'text_hover'       => array(),
				'text_link2'       => array(),
				'text_hover2'      => array(),
				'text_link3'       => array(),
				'text_hover3'      => array(),
				'alter_link'       => array(),
				'alter_hover'      => array(),
				'alter_link2'      => array(),
				'alter_hover2'     => array(),
				'alter_link3'      => array(),
				'alter_hover3'     => array(),
				'extra_link'       => array(),
				'extra_hover'      => array(),
				'extra_link2'      => array(),
				'extra_hover2'     => array(),
				'extra_link3'      => array(),
				'extra_hover3'     => array(),
			)
		);

		// Parameters to set order of schemes in the css
		jacqueline_storage_set(
			'schemes_sorted', array(
				'color_scheme',
				'header_scheme',
				'menu_scheme',
				'sidebar_scheme',
				'footer_scheme',
			)
		);

		// Color presets
		jacqueline_storage_set(
			'color_presets', array(
				'autumn' => array(
								'title'  => esc_html__( 'Autumn', 'jacqueline' ),
								'colors' => array(
												'default' => array(
																	'text_link'  => '#d83938',
																	'text_hover' => '#f2b232',
																	),
												'dark' => array(
																	'text_link'  => '#d83938',
																	'text_hover' => '#f2b232',
																	)
												)
							),
				'green' => array(
								'title'  => esc_html__( 'Natural Green', 'jacqueline' ),
								'colors' => array(
												'default' => array(
																	'text_link'  => '#75ac78',
																	'text_hover' => '#378e6d',
																	),
												'dark' => array(
																	'text_link'  => '#75ac78',
																	'text_hover' => '#378e6d',
																	)
												)
							),
			)
		);
	}
}