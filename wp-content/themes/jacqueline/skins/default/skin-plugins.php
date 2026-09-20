<?php
/**
 * Required plugins
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.76.0
 */

// THEME-SUPPORTED PLUGINS
// If plugin not need - remove its settings from next array
//----------------------------------------------------------
if ( ! function_exists( 'jacqueline_skin_required_plugins' ) ) {
	add_action( 'after_setup_theme', 'jacqueline_skin_required_plugins', -1 );
	/**
	 * Create the list of required plugins for the skin/theme.
	 * Priority -1 is used to create the list of plugins before the rest skin/theme actions.
	 * 
	 * @hooked 'after_setup_theme', -1
	 */
	function jacqueline_skin_required_plugins() {
		$jacqueline_theme_required_plugins_groups = array(
		'core'          => esc_html__( 'Core', 'jacqueline' ),
		'page_builders' => esc_html__( 'Page Builders', 'jacqueline' ),
		'ecommerce'     => esc_html__( 'E-Commerce & Donations', 'jacqueline' ),
		'socials'       => esc_html__( 'Socials and Communities', 'jacqueline' ),
		'events'        => esc_html__( 'Events and Appointments', 'jacqueline' ),
		'content'       => esc_html__( 'Content', 'jacqueline' ),
		'other'         => esc_html__( 'Other', 'jacqueline' ),
		);
		$jacqueline_theme_required_plugins        = array(
			'trx_addons'                 => array(
				'title'       => esc_html__( 'ThemeREX Addons', 'jacqueline' ),
				'description' => esc_html__( "Will allow you to install recommended plugins, demo content, and improve the theme's functionality overall with multiple theme options", 'jacqueline' ),
				'required'    => true,
				'logo'        => 'trx_addons.png',
				'group'       => $jacqueline_theme_required_plugins_groups['core'],
			),
			'elementor'                  => array(
				'title'       => esc_html__( 'Elementor', 'jacqueline' ),
				'description' => esc_html__( "Is a beautiful PageBuilder, even the free version of which allows you to create great pages using a variety of modules.", 'jacqueline' ),
				'required'    => false,
				'logo'        => 'elementor.png',
				'group'       => $jacqueline_theme_required_plugins_groups['page_builders'],
			),
			'gutenberg'                  => array(
				'title'       => esc_html__( 'Gutenberg', 'jacqueline' ),
				'description' => esc_html__( "It's a posts editor coming in place of the classic TinyMCE. Can be installed and used in parallel with Elementor", 'jacqueline' ),
				'required'    => false,
				'install'     => false,          // Do not offer installation of the plugin in the Theme Dashboard and TGMPA
				'logo'        => 'gutenberg.png',
				'group'       => $jacqueline_theme_required_plugins_groups['page_builders'],
			),
			'js_composer'                => array(
				'title'       => esc_html__( 'WPBakery PageBuilder', 'jacqueline' ),
				'description' => esc_html__( "Popular PageBuilder which allows you to create excellent pages", 'jacqueline' ),
				'required'    => false,
				'install'     => false,          // Do not offer installation of the plugin in the Theme Dashboard and TGMPA
				'logo'        => 'js_composer.jpg',
				'group'       => $jacqueline_theme_required_plugins_groups['page_builders'],
			),
			'woocommerce'                => array(
				'title'       => esc_html__( 'WooCommerce', 'jacqueline' ),
				'description' => esc_html__( "Connect the store to your website and start selling now", 'jacqueline' ),
				'required'    => false,
				'logo'        => 'woocommerce.png',
				'group'       => $jacqueline_theme_required_plugins_groups['ecommerce'],
			),
			'yith-woocommerce-gift-cards'                     => array(
				'title'       => esc_html__( 'Yith Woocommerce Gift Cards', 'jacqueline' ),
				'description' => '',
				'required'    => false,
				'logo'        => jacqueline_get_file_url( 'plugins/yith-woocommerce-gift-cards/yith-woocommerce-gift-cards.png' ),
				'group'       => $jacqueline_theme_required_plugins_groups['ecommerce'],
			),
			'elegro-payment'             => array(
				'title'       => esc_html__( 'Elegro Crypto Payment', 'jacqueline' ),
				'description' => esc_html__( "Extends WooCommerce Payment Gateways with an elegro Crypto Payment", 'jacqueline' ),
				'required'    => false,
				'install'     => false, // TRX_addons has marked the "Elegro Crypto Payment" plugin as obsolete and no longer recommends it for installation, even if it had been previously recommended by the theme
				'logo'        => 'elegro-payment.png',
				'group'       => $jacqueline_theme_required_plugins_groups['ecommerce'],
			),
			'instagram-feed'             => array(
				'title'       => esc_html__( 'Instagram Feed', 'jacqueline' ),
				'description' => esc_html__( "Displays the latest photos from your profile on Instagram", 'jacqueline' ),
				'required'    => false,
				'logo'        => 'instagram-feed.png',
				'group'       => $jacqueline_theme_required_plugins_groups['socials'],
			),
			'mailchimp-for-wp'           => array(
				'title'       => esc_html__( 'MailChimp for WP', 'jacqueline' ),
				'description' => esc_html__( "Allows visitors to subscribe to newsletters", 'jacqueline' ),
				'required'    => false,
				'logo'        => 'mailchimp-for-wp.png',
				'group'       => $jacqueline_theme_required_plugins_groups['socials'],
			),
			'booked'                     => array(
				'title'       => esc_html__( 'Booked Appointments', 'jacqueline' ),
				'description' => '',
				'required'    => false,
				'install'     => false,
				'logo'        => 'booked.png',
				'group'       => $jacqueline_theme_required_plugins_groups['events'],
			),
			'quickcal'                     => array(
				'title'       => esc_html__( 'QuickCal', 'jacqueline' ),
				'description' => '',
				'required'    => false,
				'logo'        => 'quickcal.png',
				'group'       => $jacqueline_theme_required_plugins_groups['events'],
			),
			'bookly-responsive-appointment-booking-tool'                     => array(
				'title'       => esc_html__( 'Bookly Appointments', 'jacqueline' ),
				'description' => '',
				'required'    => false,
				'logo'        => jacqueline_get_file_url( 'plugins/bookly-responsive-appointment-booking-tool/bookly-responsive-appointment-booking-tool.png' ),
				'group'       => $jacqueline_theme_required_plugins_groups['events'],
			),
			'the-events-calendar'        => array(
				'title'       => esc_html__( 'The Events Calendar', 'jacqueline' ),
				'description' => '',
				'required'    => false,
				'logo'        => 'the-events-calendar.png',
				'group'       => $jacqueline_theme_required_plugins_groups['events'],
			),
			'contact-form-7'             => array(
				'title'       => esc_html__( 'Contact Form 7', 'jacqueline' ),
				'description' => esc_html__( "CF7 allows you to create an unlimited number of contact forms", 'jacqueline' ),
				'required'    => false,
				'logo'        => 'contact-form-7.png',
				'group'       => $jacqueline_theme_required_plugins_groups['content'],
			),
			'advanced-popups'                  => array(
				'title'       => esc_html__( 'Advanced Popups', 'jacqueline' ),
				'description' => '',
				'required'    => false,
				'logo'        => jacqueline_get_file_url( 'plugins/advanced-popups/advanced-popups.jpg' ),
				'group'       => $jacqueline_theme_required_plugins_groups['content'],
			),
			'devvn-image-hotspot'                  => array(
				'title'       => esc_html__( 'Image Hotspot by DevVN', 'jacqueline' ),
				'description' => '',
				'required'    => false,
				'logo'        => jacqueline_get_file_url( 'plugins/devvn-image-hotspot/devvn-image-hotspot.png' ),
				'group'       => $jacqueline_theme_required_plugins_groups['content'],
			),
			'ti-woocommerce-wishlist'                  => array(
				'title'       => esc_html__( 'TI WooCommerce Wishlist', 'jacqueline' ),
				'description' => '',
				'required'    => false,
				'logo'        => jacqueline_get_file_url( 'plugins/ti-woocommerce-wishlist/ti-woocommerce-wishlist.png' ),
				'group'       => $jacqueline_theme_required_plugins_groups['ecommerce'],
			),
			'woo-smart-quick-view'                  => array(
				'title'       => esc_html__( 'WPC Smart Quick View for WooCommerce', 'jacqueline' ),
				'description' => '',
				'required'    => false,
				'install'     => false,
				'logo'        => jacqueline_get_file_url( 'plugins/woo-smart-quick-view/woo-smart-quick-view.png' ),
				'group'       => $jacqueline_theme_required_plugins_groups['ecommerce'],
			),
			'essential-grid'             => array(
				'title'       => esc_html__( 'Essential Grid', 'jacqueline' ),
				'description' => '',
				'required'    => false,
				'install'     => false,
				'logo'        => 'essential-grid.png',
				'group'       => $jacqueline_theme_required_plugins_groups['content'],
			),
			'revslider'                  => array(
				'title'       => esc_html__( 'Revolution Slider', 'jacqueline' ),
				'description' => '',
				'required'    => false,
				'logo'        => 'revslider.png',
				'group'       => $jacqueline_theme_required_plugins_groups['content'],
			),
			'sitepress-multilingual-cms' => array(
				'title'       => esc_html__( 'WPML - Sitepress Multilingual CMS', 'jacqueline' ),
				'description' => esc_html__( "Allows you to make your website multilingual", 'jacqueline' ),
				'required'    => false,
				'install'     => false,      // Do not offer installation of the plugin in the Theme Dashboard and TGMPA
				'logo'        => 'sitepress-multilingual-cms.png',
				'group'       => $jacqueline_theme_required_plugins_groups['content'],
			),
			'wp-gdpr-compliance'         => array(
				'title'       => esc_html__( 'Cookie Information', 'jacqueline' ),
				'description' => esc_html__( "Allow visitors to decide for themselves what personal data they want to store on your site", 'jacqueline' ),
				'required'    => false,
				'install'     => false,
				'logo'        => 'wp-gdpr-compliance.png',
				'group'       => $jacqueline_theme_required_plugins_groups['other'],
			),
			'gdpr-framework'         => array(
				'title'       => esc_html__( 'The GDPR Framework', 'jacqueline' ),
				'description' => esc_html__( "Tools to help make your website GDPR-compliant. Fully documented, extendable and developer-friendly.", 'jacqueline' ),
				'required'    => false,
				'install'     => false,
				'logo'        => 'gdpr-framework.png',
				'group'       => $jacqueline_theme_required_plugins_groups['other'],
			),
			'trx_updater'                => array(
				'title'       => esc_html__( 'ThemeREX Updater', 'jacqueline' ),
				'description' => esc_html__( "Update theme and theme-specific plugins from developer's upgrade server.", 'jacqueline' ),
				'required'    => false,
				'logo'        => 'trx_updater.png',
				'group'       => $jacqueline_theme_required_plugins_groups['other'],
			),
		);

		if ( JACQUELINE_THEME_FREE ) {
			unset( $jacqueline_theme_required_plugins['js_composer'] );
			unset( $jacqueline_theme_required_plugins['booked'] );
			unset( $jacqueline_theme_required_plugins['quickcal'] );
			unset( $jacqueline_theme_required_plugins['the-events-calendar'] );
			unset( $jacqueline_theme_required_plugins['calculated-fields-form'] );
			unset( $jacqueline_theme_required_plugins['essential-grid'] );
			unset( $jacqueline_theme_required_plugins['revslider'] );
			unset( $jacqueline_theme_required_plugins['sitepress-multilingual-cms'] );
			unset( $jacqueline_theme_required_plugins['trx_updater'] );
			unset( $jacqueline_theme_required_plugins['trx_popup'] );
		}

		// Add plugins list to the global storage
		jacqueline_storage_set( 'required_plugins', $jacqueline_theme_required_plugins );
	}
}